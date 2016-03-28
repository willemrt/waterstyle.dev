Version 1.4.2
  - Bug-fix: Added case for widget translation for when title sub-field is not explicitly declared
  
-------------------------------------------------------------------------------------------------------------------
 
Version 1.4.1
  - Feature: Added "show more" functionality to "Show where used" box in Layouts editor to show paginated assignment
  - Compatibility: Addeded generic handler for save post hook to handle automatic layouts assignment regardless the post is created
  - Compatibility: Addeded filter to add additional row mode GUI
  - Compatibility: Addeded filter to override row rendering in front end
  - Compatibility: Addeded filter to push new Layouts compatible templates from plugins
  - Compatibility: Addeded filter to force assignment dialog to render single posts and pages even if no compatible templates are present in theme's folder
  - Refactoring: Rewritten all default Layouts cells in Object Oriented Programming style
  - Bug-fix: Fixed bug in CSS sanitisation
  
-------------------------------------------------------------------------------------------------------------------

Version 1.4
  - Feature: Implemented Toolset resources duplication when duplicating a layout and new GUI to allow granular control on what's going to be duplicate
  - Feature: New Image Box cell implementing full Wordpress and Bootstrap features
  - User-interface: Moved CSS Editor in a separate admin screen
  - User-interface: Brand new and simplified GUI for Content Template cell
  - User-interface: Improved controls organisation for Content Template cell, existing content templates are presented first to prevent useless elements creation
  - User-interface: Brand new and simplified GUI for Comment cell
  - User-interface: Brand new and simplified GUI for Views cell
  - User-interface: Brand new and simplified GUI for Grid cell
  - User-interface: Brand new and simplified GUI for Child Layout cell
  - User-interface: Removed cells and rows names inputs in favor of automatic generation of names
  - Compatibility: Brand new Layouts Framework API to run Layouts with CSS Framework other than Bootstrap (expertimental)
  - Bug-fix: Fixed a bug with CRED edit mode when rendering the CRED form in a Content Template cell
  - Bug-fix: Fixed method to check whether a php template supports Layouts or not in child themes
  - Bug-fix: Fixed method to check whether a layout is assigned to Blog page when not Home
  - Bug-fix: Fixed compatibility with php 5.2.x and prevented fatal errors
  - Bug-fix: various bug fixing
  - Security: Added programmatic sanitization of Layouts properties against SSI injection
  - Security: Improved editor nonce/referrer check when layout data is saved to the database
 
------------------------------------------------------------------------------------------------------------------- 
  
Version 1.3.3
  - Bug-fix: Fixed compatibility problem with CRED 1.4.x
    
------------------------------------------------------------------------------------------------------------------- 

Version 1.3.2
  - Bug-fix: Fixed undefined index in Layouts renderer when Content Template cell does not exist in current Layout rendered

------------------------------------------------------------------------------------------------------------------- 

Version 1.3.1
  - Bug-fix: Fixed problem with Fields dialog positioning when opened in iFrame
  - Bug-fix: Fixed javascript error when Content Template cell has been deleted outside of Layouts
  - Bug-fix: Prevented javascript errors when Cells have a numeric name or name is not of string type 
  
------------------------------------------------------------------------------------------------------------------- 

Version 1.3
  - Feature: Brand new CRED User Form cell
  - Feature: WPML Language Switcher integration for "Change Use Dialog"
  - Feature: Brand new Settings Page
  - Feature: Enable disable cells with the as they were features, with the Feature API
  - Feature: Show / hide "Design with Toolset" Admin Toolbar button from Layouts Settings
  - Feature: Control the maximum number of posts to be refreshed when a layout has been saved - for caching plugin users
  - User-interface: "Change Use Dialog" shows content items in the right language
  - User-interface: Renewed and improved feedback GUI when importing layouts
  - User-interface: Added fatal error handler in case a fatal error occurs during import
  - User-interface: Disabled CSS tab and provided courtesy message in Child Layout cell and row dialog, when they don't render in front-end
  - User-interface: Improved "Remove layout assignement" GUI
  - User-interface: Added courtesy feedback message to the users in Listing Page, when they don't have the rights to perform an action
  - Compatibility: Further empowerment of WPML integration
  - Compatibility: Full compatibility with Module Manager
  - Compatibilty: Full compatibility with Woocommerce / Woocommerce Multilingual
  - Compatibility: Integration with caching plugins to refresh posts status automatically once the layout assigned to the resource has been edited
  - Performance: Improved performance in import layouts script
  - Performance: Improved performance to populate Layouts selector in post edit page
  - Bug-fix: Fixed bug with private and password protected pages when layout is assigned
  - Bug-fix: Fixed bug with second level links in menu cell on IOS platforms
  - Bug-fix: Fixed conflict between CRED and Content Template cell in front-end rendering, and generally improved compatibility with CRED
  - Bug-fix: Fixed graphic bug in CRED Form dialog and face-lift dialog GUI
  - Bug-fix: Fixed Visual Editor cell bug causing conflicts with WPML String Translation module
  - Bug-fix: Fixed bug causing pages loosing their layouts when edited with CRED Form
  - Bug-fix: Fixed bug causing posts to be created without their layouts - if assigned to their entire post type - if created with Types parent / child GUI
  - Bug-fix: Fixed bug causing a javascript error when a Child Layout cell is edited from the parent
  - Bug-fix: Fixed javascript error in front end when using ajax pagination with Comments cell
  - Bug-fix: Fixed javascript error in editor when Visual Editor cell is disabled


-----------------------------------------------------------------------------------------------------------------
Version 1.2
  - User-interface: New "Design with Toolset" button to help select the right tool to design given resource content rendering
  - User-interface: Added overlay to highlight editable area in Layouts editor
  - User-interface: Added progressive loading to populate posts selectors in Content Template cell for performance and usability
  - User-interface: Improved interaction in Content Template cells dialog to avoid possible input errors
  - User-interface: Renewed and improved GUI to remove single pages assignments
  - User-interface: Renewed and improved post edit page template/layouts selector to help select the right template for the given content
  - User-interface: Added Undo / Redo functionality to CSS editor
  - Performance: Reduced number of render calls and optimised the Layouts editor loading process
  - Performance: Implemented full background saving to allow users to perform actions during all ajax calls
  - API: Added filters to override $posts assignments at the time of database save
  - API: Added filters to override layout settings when saved to the database
  - API: Added filters to override layout $post data when saved to the database
  - API: Added new functions to the fields API to retrieve and print images handled by Layouts based on sizes (thumbnail, medium, large)
  - Compatibility: Created helper to clean orphaned Content Template cells and keep cells data consistent with Views data
  - Compatibility: Fixed compatibility issue with new Views fields insertion process
  - Compatibility: Fixed compatibility issue with other plugins using Underscore templates
  - Compatibility: Fixed compatibility problem with Views Embedded content templates
  - Compatibility: Implemented full compatibility with WPML
  - Compatibility: Implemented automatic layouts assignment for $post translations
  - Compatibility: Improved compatibility with Toolset Starter theme
  - Compatibility: Added ukraine language
  - Security: Improved method to load custom cells from theme to enhance security and prevent errors
  - Security: Added method to strip all html tags from Layouts element names to prevent malicious code injection
  - Bug-fix: Fixed Visual Editor cell tinyMCE bug when inserting a link
  - Bug-fix: Fixed problem when saving Content Template with empty name
  - Bug-fix: Fixed inconsistent height related to cell content issue for Content Template cell and Visual Editor cell
  - Bug-fix: Fixed inconsistent height issue of cells in a Row in relation to highest

------------------------------------------------------------------------------------------------------------------
Version 1.1
  - Feature: Added Embedded Mode to integrate Layouts in your theme
  - Feature: Improved import / export functions to update existing Layouts when importing
  - Feature: Added Layouts capabilities to create custom user roles
  - Feature: Added support for the Attachment (Media) Post Type
  - Feature: Full translation of the Layouts plugin in 5 languages
  - User-interface: Added new mode for HTML editor in Visual Editor cell
  - User-interface: Improved text editor for Visual Editor cell to make it more flexible and usable
  - User-interface: Added loading overlay and better user feedback for "Change layout use" dialog first loading
  - User-interface: Re-styled the Layouts Editor GUI
  - User-interface: Added keyboard interactions in cells creation dialog
  - API: Added hooks for cells render action (ddl_before_frontend_render_cell, ddl_after_frontend_render_cell)
  - API: Added hook for cells render filter (ddl_render_cell_content)
  - API: Added API function to check if a resource is assigned to a layout (is_ddlayout_assigned) working with any WordPress resource type
  - API: Added API functions to check current user capabilities in templates
  - API: Made general re-factoring of the code base and improved class organization
  - Compatibility: Added full Integration with Access Plugin
  - Compatibility: Integrated automatic resources updates for themes based on Layouts
  - Compatibility: Empowered WPML integration (still ongoing)
  - Compatibility: Added better integration with child themes
  - Security: Made full security review, every CRUD operation checks user capabilities
  - Bug-fix: Improved interaction when editing rows and row names
  - Bug-fix: Improved dialog iFrame display on narrow screens
  - Bug-fix: Added support for any tag in the Content Template cell text editor
  - Bug-fix: Improved and simplified Layout assignment in other languages (WPML integration)
  - Bug-fix: Added possibility to un-assign layout that was assigned to a deleted / missing post type
  - Bug-fix: Improved usability of iFrame based dialogs on small screens
  - Bug-fix: Added support for retro compatibility with PHP < 5.3
  - Bug-fix: Added more consistent "Change Layout use" dialog behaviour
  - Bug-fix: Fixed inconsistent behaviour for "Change layout use" dialog in post types section
  - Bug-fix: Fixed "Change layout use" dialog display bug when WPML is active

-------------------------------------------------------------------------------------------------------------------
Version 1.0
   - New: CRED Cell
   - New: Comments cell
   - New: New Menu cell (Responsive Menu Cell)
   - New: Translation of cells via WPML
   - New: Improvements to the Image Box cell
   - New: Improvements to the Menu cell
   - New: Removed the Post Content cell (users should use the Content Template cell instead)
   - New: Removed the Post Loop cell (users should use the WordPress Archive cell instead)
   - New: Removed the Widget Zone cell (users should use a grid of Widget cells instead)
   - New: Added container-fluid to rows when rendering
   - New: Overlay added to the post body when the associated layout doesn't include cells or shortcodes that display the post body
   - New: Improved workflow when assigning layouts to content
   - New: Import and export now includes data to associate layouts with content
   - New: Merged all the Views cells into one
   - New: New preview graphic design
   - New: New Create cell dialog design
   - New: New graphic design for cells' icons
   - New: Improved interactions in the create post page
   - New: Support for the embedded version of the CRED plugin
   - New: Improved the way Views cells are connected to their Views
   - New: Added Layouts cell information to the Views iframes
   - New: New where used listing box in the layout editing page
   - New: Improved cell descriptions and naming
   - New: Improved workflow for assigning parent layouts
   - New: Added quicktags to the editor in the Content Template cell
   - New: Improved integration with the Woocommerce Views plugin
   - New: New and improved caching system for PHP objects
   - New: Improved and optimised database queries
   - New: Updated the supported demo theme (BS3) to the latest Bootstrap version
   - New: Improved the preview system during editing
   - New: New and improved graphics for parametric search box and results
   - Fix: Content Template overlay works with multiple cells
   - Fix: Fixed links behaviour in third level of a responsive Menu cell
   - Fix: Fixed shortcode rendering for the Content Template cell and the Visual Editor cell
   - Fix: Fixed bugs in the post edit page templates / layouts combo-box
   - Fix: Improved integration with other Toolset libraries
   - Fix: Fixed bugs and improved layouts creation from the post edit page
   - Fix: Allow to create new layouts for post drafts
   - Fix: Fixed bugs related to the assignment import process for taxonomy and post types archives

-------------------------------------------------------------------------------------------------------------------
Version 0.9.2
   - New: Content Template cell (separate from Post Content cell)
   - New: Improved workflow for Views Conent Grid cell
   - New: Automatically create a Content Template to use with a View
   - New: Post Loop cell for archive pages
   - New: Views Post Loop cell for archive pages
   - New: Layout Assignment method
   - New: Layouts can be assigned to archives
   - New: Layouts can be assigned to 404 page
   - New: Better organized Cell Select dialog
   - New: Callback for Post Content cell for theme integration
   - New: Callback for Post Loop cell for theme integration
   - Fix: Problems with Unicode in Layout name
   - Fix: Problems with Unicode when duplicating Layouts
   - Fix: CRED forms not working with Layouts
   - Fix: Grid size selector of 6
   - Fix: Remove calls to Multibyte string library
   

-------------------------------------------------------------------------------------------------------------------
Version 0.9.1
   - New: New listing page
   - New: Show layout hierarchy in listing page
   - New: Post content cell allows creation and editing of Content Templates
   - New: New Views Content Grid cell with close Views integration
   - Fix: WP 3.9 support
   - Fix: support quotes in CSS file
   - Fix: use quotes in Layout names
   - Fix: loading css resources
   - Fix: hierarchical layouts on BS3 theme
   - Fix: codemirror conflicts with Views
   - Fix: bulk actions on listing page
   - Fix: don't allow parents to be assigned to post types or individual posts
   - Fix: handling of class names in CSS editor
   - Fix: many PHP warnings
   - Fix: importing of CSS from themes
   


-------------------------------------------------------------------------------------------------------------------
Version 0.9.0

First Beta release