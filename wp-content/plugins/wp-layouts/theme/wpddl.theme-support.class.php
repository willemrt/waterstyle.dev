<?php
define('archives', 'archives');
define('post_types', 'post_types');
define('posts', 'posts');
define('attachments', 'attachments');

add_action('init', 'init_layouts_theme_support', 9);

function init_layouts_theme_support()
{
    global $wpddlayout_theme;
    $wpddlayout_theme = WPDD_Layouts_Theme::getInstance();
}

class WPDD_Layouts_Theme
{

    private $messages = array();
    private $layouts_saved = 0;
    private $css_saved = 0;
    private $layouts_deleted = 0;
    private $layouts_overwritten = 0;
    private $images_created = 0;
    private $existing_layout = null;
    private $media = null;
    // keep track of the posts successfully assigned to imported layout
    //  private $posts_assigned = array();
    // keep track of the posts not present in the new DB which cannot be assigned
    //  private $post_not_assigned = array();

    private $imported_layouts = array();
    private $track_for_mm = array();
    private static $instance;

    function __construct()
    {

        $this->file_manager_export = new WPDD_FileManager('/theme-dd-layouts', 'wp_nonce_export_layouts_to_theme');

        if (is_admin()) {
            if (isset($_GET['page']) && $_GET['page'] == 'dd_layout_theme_export') {
                add_action('wp_loaded', array($this, 'export_and_download_layouts'));
                add_action('wp_loaded', array($this, 'import_layouts'));
            }
        }

        add_action('wp_ajax_dll_import_layouts', array($this, 'import_layouts_ajax_callback'));
        add_action('toolset-shutdown-hander', array(&$this, 'shut_down_handler'));
        add_filter('ddl-fix_toolset_association_on_import_export-data', array($this, 'set_toolset_association_default_args'), 8, 3);
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new WPDD_Layouts_Theme();
        }

        return self::$instance;
    }

    function shut_down_handler()
    {
        $text = __('The Layouts files you are trying to upload are too big and you ran out of memory, you can either increase your memory or reduce the size of the import.', 'ddl-layouts');

        if( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ){
            die('');
        } else {
            printf('<div class="alert-no-post-content toolset-alert toolset-alert-error">%s</div>', $text);
        }

    }


    function import_layouts_ajax_callback()
    {
        if (user_can_edit_layouts() === false) {
            die(WPDD_Utils::ajax_caps_fail(__METHOD__));
        }
        //if ( !wp_verify_nonce($_POST['nonce'], 'layouts-import-nonce') ){
        if (!wp_verify_nonce($_POST['layouts-import-nonce'], 'layouts-import-nonce')) {
            die(WPDD_Utils::ajax_nonce_fail(__METHOD__));
        }
        /**
         * TODo:
         * Show message for old browsers
         ***/
        $message = '';
        $status = 'ok';
        $continue = 'false';
        $stop_file = '';
        $overwritten = 0;
        $deleted = 0;
        $saved_css = 0;
        $saved_layouts = 0;
        $next_file = '';
        $total_files = 0;
        $is_zip = 0;
        $file_name = '';


        $overwrite = ($_POST['layouts_overwrite'] == 'false' ? false : true);
        $delete = ($_POST['layouts_delete'] == 'false' ? false : true);
        $overwrite_assignment = ($_POST['overwrite_layouts_assignment'] == 'false' ? false : true);

        if (isset($_FILES['import-file']) || isset($_POST['file'])) {

            $first_import = true;
            if (isset($_FILES['import-file'])) {
                $upload_dir = wp_upload_dir();
                $file_name = $upload_dir['path'] . '/' . $_FILES['import-file']['name'];
                move_uploaded_file($_FILES['import-file']['tmp_name'], $file_name);
                $file['tmp_name'] = $file_name;
            } else {
                $file['tmp_name'] = $_POST['file_name'];
                $first_import = false;
                $file_name = stripcslashes($_POST['file_name']);
                $next_file = stripcslashes($_POST['file']);
                if (isset($_POST['skip_file'])) {
                    $skip_file = $_POST['skip_file'];
                }
            }

            $info = pathinfo($file_name);


            if ($info['extension'] == 'zip') {
                //Return total and list of files from zip
                if (isset($_FILES['import-file'])) {
                    $zip_file_list = array();
                    $zip = zip_open($file['tmp_name']);
                    if (is_resource($zip)) {
                        $is_zip = 1;
                        while (($zip_entry = zip_read($zip)) !== false) {
                            if (self::get_extension(zip_entry_name($zip_entry)) === 'ddl' || self::get_extension(zip_entry_name($zip_entry)) === 'css') {
                                $total_files++;
                                $zip_file_list[] = zip_entry_name($zip_entry);
                            }
                        }
                    } else {
                        $message = __('Incorrect zip file.', 'ddl-layouts');
                        $status = 'error';
                    }
                    $out = array(
                        'message' => $message,
                        'status' => $status,
                        'total_files' => $total_files,
                        'file_name' => $file_name,
                        'file_list' => $zip_file_list
                    );
                    die(wp_json_encode($out));
                }


                $zip = zip_open($file['tmp_name']);
                if (is_resource($zip)) {

                    if (isset($_POST['imported_layouts'])) {
                        $this->imported_layouts = $_POST['imported_layouts'];
                    }


                    while (($zip_entry = zip_read($zip)) !== false) {
                        if ($continue == 'true' && (self::get_extension(zip_entry_name($zip_entry)) === 'ddl' || self::get_extension(zip_entry_name($zip_entry)) === 'css')) {
                            zip_close($zip);
                            break;
                        }
                        if (!empty($next_file) && $next_file != zip_entry_name($zip_entry)) {
                            continue;
                        }

                        if (self::get_extension(zip_entry_name($zip_entry)) === 'ddl') {
                            $data = @zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                            $name = self::get_file_nicename(zip_entry_name($zip_entry));
                            $this->layout_handle_save($data, $name, $overwrite, $delete, $overwrite_assignment);
                            $message = __(sprintf('File %s processed', zip_entry_name($zip_entry)), 'ddl-layouts');
                        } elseif (self::get_extension(zip_entry_name($zip_entry)) === 'css') {
                            $data = @zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                            $this->save_css($data, $overwrite);
                            $message = __(sprintf('File %s processed', zip_entry_name($zip_entry)), 'ddl-layouts');
                        }
                        if (self::get_extension(zip_entry_name($zip_entry)) === 'ddl' || self::get_extension(zip_entry_name($zip_entry)) === 'css') {
                            $continue = 'true';

                            if (isset($_POST['last_file']) && $_POST['last_file'] == 1 && $delete) {
                                if ($delete) {
                                    $this->handle_layouts_to_be_deleted();
                                }
                                zip_close($zip);
                                if (file_exists($file_name)) {
                                    unlink($file_name);
                                }
                                break;
                            }
                        }

                    }


                } else {
                    $message = __('Incorrect zip file.', 'ddl-layouts');
                    $status = 'error';
                }

            } else if ($info['extension'] == 'ddl') {

                $result = $this->handle_single_layout($file, $info, $overwrite, $delete, $overwrite_assignment);

                if ($delete) {
                    $this->handle_layouts_to_be_deleted();
                }
                if (file_exists($file_name)) {
                    unlink($file_name);
                }
                $message = "Working with " . $_FILES['import-file']['name'];
            } else if ($info['extension'] == 'css') {
                global $wpddlayout;
                $data = file_get_contents($file['tmp_name']);
                $css = $wpddlayout->css_manager->get_layouts_css();
                if ($data == $css) {
                    $message = __("The CSS you're trying to import is the same as saved in database.", 'ddl-layouts');
                    $status = 'error';
                } elseif (!empty($data) && !$overwrite) {
                    $message = __("Layouts already has CSS styling. Check &quotOverwrite any layout if it already exists&quot, if you want to overwrite this CSS.", 'ddl-layouts');
                    $status = 'error';
                } else {
                    $result = $this->handle_single_css($file, $overwrite);

                    if ($result === false) {
                        $message = __('There was a problem saving the CSS.', 'ddl-layouts');
                        $status = 'error';
                    } else {
                        if ($overwrite === false) {
                            $css_message = __('The Layouts CSS was created.', 'ddl-layouts');
                        } else {
                            $css_message = __('The Layouts CSS was overwritten.', 'ddl-layouts');
                        }

                        $message = $css_message;

                    }
                    $this->handle_messages($result, $overwrite || $overwrite_assignment, $delete, true, $info['extension']);
                }


                if (file_exists($file_name)) {
                    unlink($file_name);
                }
            } else {
                $message = __('The file type is not compatible with layouts. The imported files should be a single .ddl file, a single .css file or a .zip archive of .ddl and .css files.', 'ddl-layouts');
                $status = 'error';
            }

        } else {
            $message = __('There was a problem uploading the file. Check the file and try again', 'ddl-layouts');
            $status = 'error';
        }

        if ($status == 'ok') {

        }
        $overwritten = $this->layouts_overwritten;
        $deleted = $this->layouts_deleted;
        $saved_css = $this->css_saved;
        $saved_layouts = $this->layouts_saved;

        $out = array(
            'message' => $message,
            'status' => $status,
            'file_name' => $file_name,
            'overwritten' => $overwritten,
            'deleted' => $deleted,
            'saved_css' => $saved_css,
            'saved_layouts' => $saved_layouts,
            'imported_layouts' => $this->imported_layouts
        );
        die(wp_json_encode($out));
    }

    function import_layouts()
    {
        if ($_POST) {
            if (isset($_POST['ddl-import']) && $_POST['ddl-import'] == __('Import', 'ddl-layouts') && isset($_POST['layouts-import-nonce']) && wp_verify_nonce($_POST['layouts-import-nonce'], 'layouts-import-nonce')) {

                $overwrite = isset($_POST['layouts-overwrite']) && $_POST['layouts-overwrite'] == 'on' ? true : false;
                $delete = isset($_POST['layouts-delete']) && $_POST['layouts-delete'] == 'on' ? true : false;
                $overwrite_assignment = isset($_POST['overwrite-layouts-assignment']) && $_POST['overwrite-layouts-assignment'] == 'on' ? true : false;

                $import = $this->manage_manual_import($_FILES, $overwrite, $delete, $overwrite_assignment);

                add_action('admin_notices', array(&$this, 'import_upload_message'));
            } else if (isset($_POST['ddl-import']) && $_POST['ddl-import'] == __('Import', 'ddl-layouts') && isset($_POST['layouts-import-nonce']) && wp_verify_nonce($_POST['layouts-import-nonce'], 'layouts-import-nonce') === false) {
                add_action('admin_notices', array(&$this, 'nonce_control_failed'));
            }
        }
    }

    function nonce_control_failed()
    { ?>
        <div class="message error">
            <p><?php _e('There was a security check issue while uploading the file. Nonce check failed.', 'ddl-layouts'); ?></p>
        </div>
        <?php
    }

    function import_upload_message()
    {
        foreach ($this->messages as $message):
            ?>
            <div class="message <?php echo $message->result; ?>"><p><?php echo $message->message; ?></p></div>
            <?php
        endforeach;
    }

    private function manage_manual_import($files, $overwrite, $delete, $overwrite_assignment)
    {
        if (isset($files['import-file'])) {
            $file = $files['import-file'];
        } else {

            $this->messages[] = (object)array(
                'result' => 'error',
                'message' => __('There was a problem uploading the file. Check the file and try again', 'ddl-layouts')
            );

            return false;
        }

        $info = pathinfo($file['name']);

        if ($info['extension'] == 'zip') {
            $result = $this->handle_zip_file($file, $overwrite, $delete, $overwrite_assignment);

            if ($delete) {
                $this->handle_layouts_to_be_deleted();
            }

            $this->handle_messages($result, $overwrite || $overwrite_assignment, $delete, true, $info['extension']);

        } else if ($info['extension'] == 'ddl') {

            $result = $this->handle_single_layout($file, $info, $overwrite, $delete, $overwrite_assignment);

            if ($delete) {
                $this->handle_layouts_to_be_deleted();
            }

            $this->handle_messages($result, $overwrite || $overwrite_assignment, $delete, true, $info['extension']);
        } else if ($info['extension'] == 'css') {
            $result = $this->handle_single_css($file, $overwrite);

            if ($result === false) {
                $this->messages[] = (object)array(
                    'result' => 'error',
                    'message' => __('There was a problem saving the CSS.', 'ddl-layouts')
                );
            } else {
                if ($overwrite === false) {
                    $css_message = __('The Layouts CSS was created.', 'ddl-layouts');
                } else {
                    $css_message = __('The Layouts CSS was overwritten.', 'ddl-layouts');
                }

                $this->messages[] = (object)array(
                    'result' => 'updated',
                    'message' => $css_message
                );
            }

            return true;
        } else {
            $this->messages[] = (object)array(
                'result' => 'error',
                'message' => __('The file type is not compatible with layouts. The imported files should be a single .ddl file, a single .css file or a .zip archive of .ddl and .css files.', 'ddl-layouts')
            );
            return false;
        }

        return true;
    }

    private function handle_single_layout($file, $info, $overwrite = false, $delete = false, $overwrite_assignment = false)
    {

        $layout_name = $info['filename'];

        $layout_json = file_get_contents($file['tmp_name']);

        $ret = $this->layout_handle_save($layout_json, $layout_name, $overwrite, $delete, $overwrite_assignment);

        return $ret === 0 ? false : true;
    }

    private function handle_single_css($file, $overwrite = false)
    {
        $data = file_get_contents($file['tmp_name']);

        $ret = $this->save_css($data, $overwrite);

        return $ret;
    }

    private function handle_messages($result, $overwrite, $delete, $extension)
    {

        $plural = __('layouts were', 'ddl-layouts');
        $singular = __('layout was', 'ddl-layouts');

        if ($this->layouts_saved !== 1) {
            $saved = $plural;
        } else {
            $saved = $singular;
        }

        if ($this->layouts_overwritten !== 1) {
            $overwritten = $plural;
        } else {
            $overwritten = $singular;
        }

        if ($this->layouts_deleted !== 1) {
            $deleted = $plural;
        } else {
            $deleted = $singular;
        }

        if ($result === false) {
            $this->messages[] = (object)array(
                'result' => 'error',
                'message' => __(sprintf('Unable to open %s file.', $extension), 'ddl-layouts')
            );

            return false;
        } else {
            if ($overwrite === false) {
                $css_message = $this->css_saved === 0 ? '' : __('The Layouts CSS was created.', 'ddl-layouts');
            } else {
                $css_message = $this->css_saved === 0 ? '' : __('The Layouts CSS was overwritten.', 'ddl-layouts');
            }


            if ($overwrite === false && $delete === false) {
                $this->messages[] = (object)array(
                    'result' => 'updated',
                    'message' => __(sprintf('%d %s imported. %s', $this->layouts_saved, $saved, $css_message), 'ddl-layouts')
                );
            } elseif ($overwrite === true && $delete === false) {
                $this->messages[] = (object)array(
                    'result' => 'updated',
                    'message' => __(sprintf('%d %s imported, %d %s overwritten. %s', $this->layouts_saved, $saved, $this->layouts_overwritten, $overwritten, $css_message), 'ddl-layouts')
                );
            } elseif ($overwrite === false && $delete === true) {
                $this->messages[] = (object)array(
                    'result' => 'updated',
                    'message' => __(sprintf('%d %s imported, %d %s deleted. %s', $this->layouts_saved, $saved, $this->layouts_deleted, $deleted, $css_message), 'ddl-layouts')
                );
            } elseif ($overwrite === true && $delete === true) {
                $this->messages[] = (object)array(
                    'result' => 'updated',
                    'message' => __(sprintf('%d %s imported, %d %s overwritten, %s %s deleted. %s', $this->layouts_saved, $saved, $this->layouts_overwritten, $overwritten, $this->layouts_deleted, $deleted, $css_message), 'ddl-layouts')
                );
            }
        }
    }

    private function handle_layouts_to_be_deleted()
    {
        if (is_array($this->imported_layouts) && count($this->imported_layouts) > 0) {
            $posts = get_posts(
                array(
                    'post_type' => WPDDL_LAYOUTS_POST_TYPE,
                    'post__not_in' => $this->imported_layouts,
                    'posts_per_page' => -1
                )
            );

            if (is_array($posts) && count($posts) > 0) {
                foreach ($posts as $post) {
                    $ret = wp_delete_post($post->ID, true);

                    if ($ret) {
                        $this->layouts_deleted++;
                    }
                }
            }
        }
    }

    function handle_zip_file($file, $overwrite, $delete, $overwrite_assignment)
    {
        $zip = zip_open(urldecode($file['tmp_name']));
        if (is_resource($zip)) {
            while (($zip_entry = zip_read($zip)) !== false) {
                if (self::get_extension(zip_entry_name($zip_entry)) === 'ddl') {
                    $data = @zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                    $name = self::get_file_nicename(zip_entry_name($zip_entry));
                    $this->layout_handle_save($data, $name, $overwrite, $delete, $overwrite_assignment);

                } elseif (self::get_extension(zip_entry_name($zip_entry)) === 'css') {
                    $data = @zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                    $this->save_css($data, $overwrite);
                }
            }

            return true;

        } else {
            return false;
        }

        return false;
    }

    function import_layouts_and_css_from_dir($dir_str, $overwrite, $delete, $overwrite_assignment)
    {

        $ret = array();
        $dir = opendir($dir_str);

        while (($currentFile = readdir($dir)) !== false) {
            $file = $dir_str . DIRECTORY_SEPARATOR . $currentFile;
            if (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) === 'ddl') {
                $name = basename($file, '.ddl');
                $data = @file_get_contents($file);
                $save = $this->layout_handle_save($data, $name, $overwrite, $delete, $overwrite_assignment);
                if ($save) {
                    $ret[] = $save;
                }
            } elseif (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) === 'css') {
                $data = @file_get_contents($file);
                $save = $this->save_css($data, $overwrite);
                if ($save) {
                    $ret[] = 'CSS';
                }
            }
        }
        closedir($dir);

        return count($ret) === 0 ? null : $ret;
    }

    public function get_import_data()
    {
        return array(
            'items' => $this->track_for_mm,
            'updated' => $this->layouts_overwritten,
            'new' => $this->layouts_saved,
            'attachments' => $this->images_created,
            'failed' => 0,
            'errors' => array(),
            WPDDL_ModuleManagerSupport::LAYOUTS_MODULE_MANAGER_CSS_ID => $this->css_saved
        );
    }

    private function save_css($data, $overwrite)
    {
        global $wpddlayout;
        $save = $wpddlayout->css_manager->import_css($data, $overwrite);
        if ($save) $this->css_saved++;
        return $save;
    }

    function get_media()
    {
        return $this->media;
    }

    public function layout_handle_save($layout_json, $layout_name, $overwrite = false, $delete = false, $overwrite_assignment = false)
    {
        if (is_object($layout_json) || is_array($layout_json)) {
            $layout = $layout_json;
        } else {
            Toolset_ErrorHandler::start(E_ALL);
            $layout = json_decode(str_replace('\\\"', '\"', $layout_json));
        }


        if (is_null($layout) === false) {

            if (is_object($layout) && isset($layout->media)) {
                $this->media = $layout->media;
                unset($layout->media);
            } elseif (is_array($layout) && isset($layout['media'])) {
                $this->media = $layout['media'];
                unset($layout['media']);
            }

            try {
                $layout_json = wp_json_encode(self::fix_toolset_association_on_import_export($layout, 'import'));
            } catch (Exception $e) {
                printf("Error: %s in %s at %d", $e->getMessage(), $e->getFile(), $e->getLine());
            }


            $this->existing_layout = self::layout_exists($layout_name);

            if ($this->media && is_array($this->media) && count($this->media) > 0) {
                $files = $this->create_and_save_media_files();
                if ($files) {
                    $layout_json = $this->handle_layout_media_url_patch($layout_json);
                }
            }

            if ($overwrite === false) {

                if ($this->existing_layout === null) {

                    $ret = $this->save_layout($layout_name, $layout_json, $layout, $overwrite_assignment);
                    if ($ret !== 0) {
                        if ($delete) $this->imported_layouts[] = $ret;
                        $this->track_for_mm[$layout->name] = $ret;
                        $this->layouts_saved++;
                    }

                    return $ret;

                } elseif ($this->existing_layout !== null) {

                    $ret = $this->manage_assignments($this->existing_layout, $layout, $overwrite_assignment);
                    if ($ret) {
                        $this->layouts_overwritten++;
                        return $ret;
                    }
                }
            } elseif ($overwrite === true) {
                if ($this->existing_layout === null) {
                    $ret = $this->save_layout($layout_name, $layout_json, $layout, $overwrite_assignment);
                    if ($ret !== 0) {
                        if ($delete) $this->imported_layouts[] = $ret;
                        $this->track_for_mm[$layout->name] = $ret;
                        $this->layouts_saved++;
                    }
                    return $ret;
                } else {
                    $ret = $this->update_layout($this->existing_layout, $layout_name, $layout_json, $layout, $overwrite_assignment);
                    if ($ret !== 0) {
                        if ($delete) $this->imported_layouts[] = $ret;
                        $this->track_for_mm[$layout->name] = $ret;
                        $this->layouts_overwritten++;
                    }
                    return $ret;
                }
            }
        }

        return false;
    }

    private function handle_layout_media_url_patch($layout_json)
    {
        if (!$this->media || count($this->media) === 0) {
            return $layout_json;
        }

        $old_root = null;

        foreach ($this->media as $media) {

            if (is_object($media) === false) {
                $media = (object)$media;
            }

            if (isset($media->root) && $media->root) {
                $old_root = $media->root;
                $layout_json = $this->patch_media_url($layout_json, $old_root);
                break;
            }
        }

        return $layout_json;
    }

    private function create_and_save_media_files()
    {
        if (!$this->media || count($this->media) === 0) {
            return false;
        }

        foreach ($this->media as $media) {

            if (is_object($media) === false) {
                $media = (object)$media;
            }

            $abs = ABSPATH;

            if (file_exists($abs . $media->path)) {
                continue;
            }

            $uploads = substr($abs . $media->dir, 0, -8);
            $uploads = $abs . $uploads;

            if (is_dir($uploads) === false) {
                @mkdir($uploads);
            }

            $year_dir = substr($abs . $media->dir, 0, -3);

            if (is_dir($year_dir) === false) {
                @mkdir($year_dir);
            }

            if (is_dir($abs . $media->dir) === false) {
                @mkdir($abs . $media->dir);
            }
            $file = @fopen($abs . $media->path, "w");

            if ($file) {
                $content = stripslashes($media->bin);
                @fwrite($file, base64_decode($content));
                fclose($file);
                $this->insert_attachment($abs . $media->path, 0);
            }
        }

        return true;
    }

    private function insert_attachment($filename, $layout_id = 0)
    {
        // Check the type of file. We'll use this as the 'post_mime_type'.
        $filetype = wp_check_filetype(basename($filename), null);

        // Get the path to the upload directory.
        $wp_upload_dir = wp_upload_dir();

        // Prepare an array of post data for the attachment.
        $attachment = array(
            'guid' => $wp_upload_dir['url'] . '/' . basename($filename),
            'post_mime_type' => $filetype['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        // Insert the attachment.
        $attach_id = wp_insert_attachment($attachment, $filename, $layout_id);

        // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        // Generate the metadata for the attachment, and update the database record.
        $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
        wp_update_attachment_metadata($attach_id, $attach_data);
        $this->images_created++;
    }


    private function patch_media_url($layout_json, $old_root)
    {
        $url = str_replace(
            '/', '\/',
            get_site_url()
        );
        $old_root = str_replace(
            '/', '\/',
            $old_root
        );
        $url = $url . "\/";
        $old_root = trim(rtrim($old_root, '"'), '"');

        return str_replace($old_root, $url, $layout_json);
    }

    private function save_layout($layout_name, $layout_json, $layout, $overwrite_assignment = false)
    {
        $postarr = array(
            'post_title' => is_object($layout) ? $layout->name : $layout['name'],
            'post_name' => $layout_name,
            'post_content' => '',
            'post_status' => 'publish',
            'post_type' => WPDDL_LAYOUTS_POST_TYPE
        );

        $post_id = wp_insert_post($postarr);

        $layout_array = json_decode($layout_json, true);

        $layout_array['id'] = $post_id;

        $this->manage_assignments($post_id, $layout, $overwrite_assignment);

        $translations = $this->get_translations($layout_array);

        WPDD_Layouts::save_layout_settings($post_id, $this->clean_up_import_data($layout_array));

        $this->set_translations($translations, $post_id);

        return $post_id;
    }

    private function clean_up_import_data($layout_array)
    {
        unset($layout_array['archives']);
        unset($layout_array['posts']);
        unset($layout_array['post_types']);
        return $layout_array;
    }

    // TODO: an option can be added to preserve status
    private function update_layout($id, $layout_name, $layout_json, $layout, $overwrite_assignment)
    {
        $postarr = array(
            'ID' => $id,
            'post_title' => is_object($layout) ? $layout->name : $layout['name'],
            'post_name' => $layout_name
        );

        $post_id = wp_update_post($postarr);

        $this->manage_assignments($post_id, $layout, $overwrite_assignment);

        $layout_array = json_decode($layout_json, true);

        $layout_array['id'] = $post_id;

        $translations = $this->get_translations($layout_array);

        WPDD_Layouts::save_layout_settings($id, $this->clean_up_import_data($layout_array));

        $this->set_translations($translations, $id);

        return $post_id;
    }


    private function manage_assignments($post_id, $layout, $overwrite_assignment)
    {
        $archives = $this->write_archive_assignments($post_id, $layout, $overwrite_assignment);
        $posts = $this->write_single_posts_assignments($post_id, $layout, $overwrite_assignment);
        $post_types = $this->write_post_types_assignments($post_id, $layout, $overwrite_assignment);
        return $archives || $posts || $post_types;
    }

    private function write_archive_assignments($post_id, $layout, $overwrite_assignment)
    {

        if (!isset($layout->archives) || count($layout->archives) === 0) return false;

        global $wpddlayout;

        $options = $wpddlayout->layout_post_loop_cell_manager->get_options_general();

        if (
            ($overwrite_assignment === true && $layout->archives && count($layout->archives) > 0) ||
            ($options === false || count($options) === 0)
        ) {
            $wpddlayout->layout_post_loop_cell_manager->handle_archives_data_save($layout->archives, $post_id);
            return $post_id;
        }

        return false;
    }

    private function write_single_posts_assignments($post_id, $layout, $overwrite_assignment)
    {

        if (!isset($layout->posts) || is_array($layout->posts) === false || count($layout->posts) === 0) return false;

        if (
        ($overwrite_assignment === true && count($layout->posts) > 0)
        ) {
            $posts = $this->get_receiving_database_posts_id($layout->posts);
            $ret = $this->assign_layout_to_single_pages($layout, $posts, $overwrite_assignment);
            if ($ret) {
                return $post_id;
            }
        }

        return false;
    }

    private function get_receiving_database_posts_id($posts_data)
    {

        $post_names_array = array();

        foreach ($posts_data as $post_data) {
            $post_names_array[] = $post_data->post_name;
        }

        global $wpddlayout;

        $posts_ids = $wpddlayout->individual_assignment_manager->fetch_posts_by_slug($post_names_array);

        if ($posts_ids === null) return null;

        foreach ($posts_data as $post_data) {
            if (isset($posts_ids[$post_data->post_name]) &&
                is_object($posts_ids[$post_data->post_name]) &&
                $posts_ids[$post_data->post_name]->post_type === $post_data->post_type
            ) {
                $post_data->ID = $posts_ids[$post_data->post_name]->ID;
            } else {
                $post_data->ID = false;
            }
        }

        return $posts_data;
    }

    private function assign_layout_to_single_pages($layout, $posts, $overwrite_assignment)
    {

        if (!$posts || is_array($posts) === false || count($posts) === 0) return false;

        $ret = array();

        if ($overwrite_assignment && $posts && count($posts) > 0) {

            global $wpddlayout;

            foreach ($posts as $post) {
                $slug = $layout->slug;
                $post_id = $post->ID;
                $post_type = $post->post_type;
                $template = property_exists($post, '_wp_page_template') ? $post->_wp_page_template : false;
                if ($post_id) {
                    //  $this->posts_assigned[] = $post->post_name;
                    $ret[] = $wpddlayout->post_types_manager->update_single_post_layout($slug, $post_id, $post_type, $template);
                } else {
                    //   $this->post_not_assigned[] = $post->post_name;
                }
            }
        }

        return count($ret) > 0;
    }

    private function write_post_types_assignments($post_id, $layout, $overwrite_assignment)
    {

        if ($overwrite_assignment === false) return $overwrite_assignment;

        if (!isset($layout->post_types) || is_object($layout->post_types) === false || count((array)$layout->post_types) === 0) return false;

        global $wpddlayout;

        $to_set = array();
        $to_bulk = array();

        foreach ($layout->post_types as $post_type => $bulk) {
            if (post_type_exists($post_type)) {

                if ($bulk) {
                    $to_bulk[] = $post_type;
                }

                $to_set[] = $post_type;
            }

        }

        $wpddlayout->post_types_manager->handle_set_option_and_bulk_at_once($post_id, $to_set, $to_bulk, false);

        return true;
    }

    private function get_translations(&$layout_array)
    {
        $translations = null;
        if (isset($layout_array['translations'])) {
            $translations = $layout_array['translations'];
            unset($layout_array['translations']);
        }

        return $translations;
    }

    private function set_translations($translations, $post_id)
    {
        global $wpddlayout;

        if ($translations) {
            $wpddlayout->register_strings_for_translation($post_id);
            do_action('wpml_set_translated_strings',
                $translations,
                array(
                    'kind' => 'Layout',
                    'name' => $post_id
                )
            );

        }

    }


    public static function layout_exists($layout_name)
    {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type=%s AND post_name=%s", WPDDL_LAYOUTS_POST_TYPE, $layout_name));
    }

    public static function get_extension($file_name)
    {
        $last = strrpos($file_name, '.');

        if ($last === false) return null;

        $extension = substr($file_name, $last + 1);

        return $extension;
    }

    public static function get_file_nicename($file_name)
    {
        $last = strrpos($file_name, '/');

        $full_name = substr($file_name, $last);

        $last_dot = strrpos($full_name, '.');

        $name = substr($full_name, 1, $last_dot - 1);

        return $name;
    }

    function export_layouts_to_theme($target_dir)
    {
        global $wpdb, $wpddlayout;

        $results = array();

        $layouts = $wpdb->get_results($wpdb->prepare("SELECT ID, post_name, post_title FROM {$wpdb->posts} WHERE post_type=%s AND post_status = 'publish'", WPDDL_LAYOUTS_POST_TYPE));

        foreach ($layouts as $layout) {

            $layout_array = WPDD_Layouts::get_layout_settings($layout->ID, true);

            if (is_null($layout_array) === false) {

                try {
                    $layout_json = wp_json_encode(self::fix_toolset_association_on_import_export($layout_array, 'export'));
                } catch (Exception $e) {
                    printf("Error: %s in %s at %d", $e->getMessage(), $e->getFile(), $e->getLine());
                }

                $post_types = $this->set_post_types_export_data($layout->ID);

                $archives = $wpddlayout->layout_post_loop_cell_manager->get_layout_loops($layout->ID);

                $posts = $this->set_layouts_associated_posts_for_export($layout->post_name, $post_types);

                $layout_array->post_types = $post_types;
                $layout_array->archives = $archives;
                $layout_array->posts = $posts;

                $translations = apply_filters('WPML_get_translated_strings',
                    array(),
                    array(
                        'kind' => 'Layout',
                        'name' => $layout->ID
                    )
                );

                if (!empty($translations)) {
                    $layout_array->translations = $translations;
                }

                $layout_json = wp_json_encode($layout_array);

                $results[] = $this->file_manager_export->save_file($layout->post_name, '.ddl', $layout_json, array('title' => $layout->post_title), true);

            }

        }

        $css = $this->get_layout_css();

        if ($css) {
            $results[] = $this->file_manager_export->save_file('layouts', '.css', $css, array('title' => 'Layouts CSS'), true);
        }

        return $results;

    }

    function get_layout_css()
    {
        global $wpddlayout;
        return $wpddlayout->get_layout_css();
    }

    /*
     * Set post type data for export. Get post type slug and track if batched.
     */
    private function set_post_types_export_data($layout_id)
    {
        global $wpddlayout;

        $post_types = $wpddlayout->post_types_manager->get_layout_post_types($layout_id);

        if (count($post_types) === 0) return null;

        $ret = array();

        foreach ($post_types as $post_type) {
            $ret[$post_type] = $wpddlayout->post_types_manager->get_post_type_was_batched($layout_id, $post_type);
        }

        return $ret;
    }

    /*
     * Filters posts assigned with entire post type.
     * If the type is not there or not batched keep, otherwise leave it remove it from collection
     */
    private function set_layouts_associated_posts_for_export($layout_slug, $post_types_data)
    {
        global $wpddlayout;
        $posts = $wpddlayout->individual_assignment_manager->fetch_layout_posts($layout_slug);

        if (is_null($posts)) {
            return null;
        }

        $ret = array();

        foreach ($posts as $post) {
            if (is_null($post_types_data) || array_key_exists($post->post_type, $post_types_data) && $post_types_data[$post->post_type] === false ||
                array_key_exists($post->post_type, $post_types_data) === false
            ) {
                $ret[] = $post;
            }
        }

        return $ret;
    }

    function export_for_download()
    {
        global $wpdb;

        $results = array();

        $layouts = $wpdb->get_results($wpdb->prepare("SELECT ID, post_name, post_title FROM {$wpdb->posts} WHERE post_type=%s AND post_status = 'publish'", WPDDL_LAYOUTS_POST_TYPE));

        foreach ($layouts as $layout) {
            $results[] = $this->build_export_data_from_post($layout);
        }

        $css = $this->get_layout_css();

        if ($css) {
            $results[] = array(
                'file_data' => $this->get_layout_css(),
                'file_name' => 'layouts.css',
                'title' => 'Layouts CSS',
            );
        }

        return $results;
    }


    public function build_export_data_from_post($layout, $args = array(post_types, archives, posts, null))
    {
        global $wpddlayout;

        $result = null;
        $post_types = null;
        $archives = null;
        $posts = null;

        $layout_array = WPDD_Layouts::get_layout_settings($layout->ID, true);

        if (is_null($layout_array) === false) {

            do_action('ddl-layout-export-processing-start', $layout_array);

            /**
             * there is a replace do it when it's lighter
             */
            if (is_array($args) && in_array(attachments, $args)) {
                $media = new WPDDL_LayoutsResources($layout_array);
            }

            try {
                $layout_array = self::fix_toolset_association_on_import_export($layout_array, 'export');
            } catch (Exception $e) {
                printf("Error: %s in %s at %d", $e->getMessage(), $e->getFile(), $e->getLine());
            }

            if (is_array($args) && in_array(post_types, $args)) {
                $post_types = $this->set_post_types_export_data($layout->ID);
                $layout_array->post_types = apply_filters('ddl-export-assigned-post-types', $post_types, $layout->ID);
            }

            if (is_array($args) && in_array(archives, $args)) {
                $archives = $wpddlayout->layout_post_loop_cell_manager->get_layout_loops($layout->ID);
                $layout_array->archives = apply_filters('ddl-export-assigned-archives', $archives, $layout->ID);
            }

            $posts = $this->set_layouts_associated_posts_for_export($layout->post_name, $post_types);

            if (is_array($args) && in_array(posts, $args)) {

                $layout_array->posts = apply_filters('ddl-export-assigned-posts', $posts, $layout->post_name, $post_types);
            }


            $translations = apply_filters('wpml_get_translated_strings',
                array(),
                array(
                    'kind' => 'Layout',
                    'name' => $layout->ID
                )
            );

            if (!empty($translations)) {
                $layout_array->translations = $translations;
            }

            /**
             * then add media at the end since they're very heavy
             */
            if (is_array($args) && in_array(attachments, $args)) {
                $layout_array->media = apply_filters('ddl-get-layout-images-array', null);
            }


            /**
             * remove the id for comparison, we rely on post_name/slug properties
             */
            do_action('ddl-layout-export-processing-end', $layout_array);

            unset($layout_array->id);

            $layout_json = apply_filters('ddl-get-exported-layout', wp_json_encode($layout_array), $layout_array);

            $file_name = $layout->post_name . '.ddl';

            $result = array(
                'file_data' => $layout_json,
                'file_name' => $file_name,
                'title' => $layout->post_title,
                'has_posts' => is_null($posts) || count($posts) === 0 ? null : $posts
            );
        }

        return $result;
    }

    function export_and_download_layouts()
    {
        if (isset($_POST['export_and_download'])) {

            $nonce = $_POST["wp_nonce_export_layouts"];

            if (WPDD_Utils::user_not_admin()) {
                die(__("You don't have permission to perform this action!", 'ddl-layouts'));
            }

            if (wp_verify_nonce($nonce, 'wp_nonce_export_layouts')) {
                $results = $this->export_for_download();

                $sitename = sanitize_key(get_bloginfo('name'));
                if (!empty($sitename)) {
                    $sitename .= '.';
                }

                require_once WPDDL_TOOLSET_COMMON_ABSPATH . '/Zip.php';

                if (class_exists('Zip')) {
                    $dirname = $sitename . 'dd-layouts.' . date('Y-m-d');
                    $zipName = $dirname . '.zip';
                    $zip = new Zip();
                    $zip->addDirectory($dirname);

                    foreach ($results as $file_data) {
                        $zip->addFile($file_data['file_data'], $dirname . '/' . $file_data['file_name']);
                    }

                    $zip->sendZip($zipName);
                }
            }
            die();
        }
    }

    public function set_toolset_association_default_args($args, $layout, $action)
    {

        $defaults = array(
            array(
                'property' => 'ddl_view_template_id',
                'post_type' => 'view-template',
                'db_field' => 'post_name'
            ),
            array(
                'property' => 'view',
                'post_type' => 'view',
                'db_field' => 'post_name'
            ),
            array(
                'property' => 'ddl_layout_view_id',
                'post_type' => 'view',
                'db_field' => 'post_name'
            ),
            array(
                'property' => 'ddl_layout_cred_id',
                'post_type' => 'cred-form',
                'db_field' => 'post_name'
            ),
            array(
                'property' => 'ddl_layout_cred_user_id',
                'post_type' => 'cred-user-form',
                'db_field' => 'post_name'
            ),
            array(
                'property' => 'target_id',
                'post_type' => '!unknown', // we will get correct post type inside function
                'db_field' => 'post_name'
            ));


        $ret = array_merge($args, $defaults);

        return $ret;
    }

    public static function fix_toolset_association_on_import_export(
        $layout, $action = 'export', $args = array()
    )
    {
        if (null === $layout) {
            throw new Exception(__(sprintf("Layout parameter should be an object, %s given.", gettype($layout)), 'ddl-layouts'));
        }

        $args = apply_filters('ddl-fix_toolset_association_on_import_export-data', $args, $layout, $action);

        if (!is_array($args) || sizeof($args) === 0) {
            throw new Exception(__(sprintf("Third argument should be an array containing at least one object with 'property', 'post_type' and 'db_field' properties. Argument has size of %d instead.", sizeof($args)), 'ddl-layouts'));
        }

        foreach ($layout as $key => $val) {
            if (is_object($val) || is_array($val)) {
                foreach ($args as $arg) {
                    $arg = (object)$arg;

                    if (is_object($val) && property_exists($val, $arg->property)) {
                        if ('export' === $action) {
                            $value = WPDD_Layouts::get_post_property_from_ID((int)$val->{$arg->property}, $arg->db_field);
                            if ($arg->property == 'target_id') {
                                $value = $value . ';' . WPDD_Layouts::get_post_property_from_ID((int)$val->{$arg->property}, 'post_type');
                            }
                        } elseif ('import' === $action) {
                            if ($arg->property == 'target_id') {
                                $temp = explode(';', $val->{$arg->property});
                                if (isset($temp[0]) && isset($temp[1])) {
                                    $val->{$arg->property} = $temp[0];
                                    $arg->post_type = $temp[1];
                                }
                            }
                            $value = WPDD_Layouts::get_post_ID_by_slug($val->{$arg->property}, $arg->post_type);
                        }

                        if ($value && is_null($value) === false) {
                            $val->{$arg->property} = $value;
                        }
                    }
                }

                try {
                    self::fix_toolset_association_on_import_export($val, $action, $args);
                } catch (Exception $e) {
                    printf("Error: %s in %s at %d", $e->getMessage(), $e->getFile(), $e->getLine());
                }
            }

            if (is_object($layout)) {
                $layout->{$key} = $val;
            } elseif (is_array($layout)) {
                $layout[$key] = $val;
            }
        }
        return $layout;
    }

    function import_layouts_from_theme($source_dir, $overwrite_assignment = false)
    {
        global $wpddlayout;

        $res = array();

        if (is_dir($source_dir)) {

            $layouts = glob($source_dir . '/*.ddl');

            if( is_array( $layouts ) === false || count( $layouts ) === 0 ){
                return array();
            }

            foreach ($layouts as $layout) {
                $file_details = pathinfo($layout);
                $layout_name = $file_details['filename'];
                $layout_json = file_get_contents($layout);
                $layout = json_decode(str_replace('\\\"', '\"', $layout_json));
                $layout->file_name = $layout_name;
                $layouts_array[] = $layout;
            }

            if( is_array( $layouts_array ) === false ) return array();

            usort( $layouts_array, array($this, 'sortLayoutsFromFile') );

            foreach ($layouts_array as $layout) {

                $layout_name = $layout->file_name;

                unset($layout->file_name);

                if (is_null($layout) === false) {

                    $layout_array = WPDD_Layouts_Theme::fix_toolset_association_on_import_export($layout, 'import');
                    // make sure we have the right data type
                    $layout_array = (array)$layout_array;

                    $id = WPDD_Layouts_Cache_Singleton::get_id_by_name($layout_name);

                    if (!$id) {

                        $postarr = array(
                            'post_title' => is_object($layout) ? $layout->name : $layout['name'],
                            'post_name' => $layout_name,
                            'post_content' => '',
                            'post_status' => 'publish',
                            'post_type' => WPDDL_LAYOUTS_POST_TYPE
                        );

                        $post_id = wp_insert_post($postarr);

                        $layout_array['id'] = $post_id;

                        $save = WPDD_Layouts::save_layout_settings($post_id, $layout_array);

                        if ($save) {
                            $res[] = $post_id;
                        }

                        if ($overwrite_assignment) {

                            //Archives
                            if (isset($layout_array['archives']) && count($layout_array['archives']) > 0) {
                                $wpddlayout->layout_post_loop_cell_manager->handle_archives_data_save($layout_array['archives'], $post_id);
                                $wpddlayout->layout_post_loop_cell_manager->handle_archives_data_save($layout_array['archives'], $post_id);
                            }

                            //Post Types
                            if (isset($layout_array['post_types']) && count($layout_array['post_types']) > 0) {
                                $to_set = array();
                                $to_bulk = array();

                                foreach ($layout->post_types as $post_type => $bulk) {
                                    if (post_type_exists($post_type)) {

                                        if ($bulk) {
                                            $to_bulk[] = $post_type;
                                        }

                                        $to_set[] = $post_type;
                                    }

                                }

                                $wpddlayout->post_types_manager->handle_set_option_and_bulk_at_once($post_id, $to_set, $to_bulk, false);
                            }
                            if (isset($layout_array['posts']) && count($layout_array['posts']) > 0) {
                                $this->write_single_posts_assignments($post_id, $layout, $overwrite_assignment);
                            }
                        }

                    }

                }

            }

            $save = $wpddlayout->css_manager->import_css_from_theme($source_dir);

            if ($save) $res[] = 'CSS';
        }

        return $res;
    }

    function sortLayoutsFromFile($a, $b)
    {
        if ((isset($b->posts) && count($b->posts) > 0) && (isset($a->posts) && count($a->posts) > 0)) {
            return 0;
        }
        if ((isset($b->posts) && count($b->posts) > 0) && (!isset($a->posts) || count($a->posts) === 0)) {
            return -1;
        } else if ((!isset($b->posts) || count($b->posts) === 0) && (isset($a->posts) && count($a->posts) > 0)) {
            return 1;
        } else {
            return -1;
        }
    }

    public function update_layouts($path, $args)
    {
        global $wpddlayout;

        if (is_dir($path) && is_array($args) && count($args) > 0) {

            $layouts = glob($path . '/*.ddl');

            foreach ($layouts as $layout) {
                $file_details = pathinfo($layout);

                $layout_json = file_get_contents($layout);

                $filtered = $this->filter_import($file_details['filename'], json_decode(str_replace('\\\"', '\"', $layout_json)), $args);

                $layout = $filtered->layout;
                $layout_name = $filtered->name;
                $action = $filtered->do;

                if (is_null($layout) === false) {

                    $id = $this->layout_handle_save($layout, $layout_name, true, false, false);

                    if ($action === 'overwrite' && $id) {
                        WPDD_Layouts::reset_toolset_edit_last($id);
                    } else if ($action === 'duplicate' && $this->existing_layout) {
                        WPDD_Layouts::reset_toolset_edit_last($this->existing_layout);
                    }
                }
            }
            $wpddlayout->css_manager->import_css_from_theme($path);
        }
    }

    private function filter_import($name, $layout, $filter)
    {
        $ret = new stdClass();
        $ret->name = $name;
        $ret->layout = $layout;
        $ret->do = null;

        if (count($filter) === 0) {
            return $ret;
        }

        /*   $me = array_filter($filter, function($item) use ($name){
                   return in_array( $name, array_values($item) );
           });*/

        // PHP < 5.3
        $me = array_filter($filter, array(new Toolset_ArrayUtils(null, $name), 'value_in_array'));

        if (empty($me)) {
            return $ret;
        }

        $switch = array_keys($me);
        $ret->do = $switch[0];

        switch ($ret->do) {
            case 'skip':
                $ret->layout = null;
                break;
            case 'overwrite':
                $ret->layout = $layout;
                break;
            case 'duplicate':
                $ret->name = $name . '_' . time();
                $layout->name = $layout->name . ' ' . date(DATE_COOKIE, time());
                $layout->slug = $ret->name;
                $ret->layout = $layout;
                break;
            default:
                $ret->layout = $layout;
                break;
        }

        return $ret;
    }
}

class WPDDL_LayoutsResources
{
    private $settings = null;
    private $json = null;
    private $images = array();

    static $regex_images = '/\"(http|https):\\\\\/\\\\\/[^ ]+(\.gif|\.jpg|\.jpeg|\.png)/siU';

    public function __construct($settings)
    {
        $this->settings = $settings;
        $this->json = wp_json_encode($this->settings);
        $this->process_cells_images();
        add_filter('ddl-get-layout-images-array', array(&$this, 'get_images'), 10, 1);
    }

    public function process_cells_images()
    {
        $exec = preg_match_all(self::$regex_images, $this->get_json(), $matches, PREG_SET_ORDER);
        if ($exec) {
            foreach ($matches as $match) {
                if ($match !== null) {
                    $image = array();
                    $raw = stripslashes(trim($match[0], '"'));
                    $index = strrpos($raw, "/");
                    $name = substr($raw, $index + 1);
                    $path = explode(get_site_url() . '/', $raw);
                    $path = $path[1];
                    if (empty($path)) {
                        continue;
                    }
                    $name = trim(rtrim($name, '"'), '"');
                    $image['name'] = $name;
                    $path = trim(rtrim($path, '"'), '"');
                    $image['path'] = $path;
                    $image['bin'] = addslashes($this->create_64($path));
                    $dir = explode($name, $path);
                    $dir = $dir[0];
                    $image['dir'] = $dir;
                    $root = explode($path, $raw);
                    $image['root'] = $root[0];
                    if (in_array($image, $this->images) === false) {
                        $this->images[] = $image;
                    }
                }
            }
            return $this->images;
        }
        return null;
    }

    public static function remove_url($data)
    {
        $url = str_replace(
            '/', '\/',
            apply_filters('ddl_make_image_url_relative', get_site_url())
        );
        return str_replace($url, '', $data);
    }

    private function create_64($path)
    {
        $root = ABSPATH;
        $file = $root . $path;

        if (file_exists($file)) {
            return base64_encode(file_get_contents($file));
        }
        return null;
    }

    public function get_media_processed_layout($to_array = true)
    {
        $json = self::remove_url($this->get_json());
        if ($to_array === false) {
            return $json;
        }
        $ret = json_decode($json);
        return $ret;
    }

    public function get_json()
    {
        return $this->json;
    }

    public function get_images($images)
    {
        $images = $this->images;
        return $images;
    }
}