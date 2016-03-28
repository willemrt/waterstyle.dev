<div class="wrap">
	<h2><i class="icon-layouts-logo ont-icon-24 ont-color-orange css-layouts-logo"></i><?php _e('Layouts Help', 'ddl-layouts'); ?></h2>

    <h3><?php _e('Documentation and Support', 'ddl-layouts'); ?></h3>
    <ul>
        <li><?php printf('<a target="_blank" href="http://wp-types.com/documentation/user-guides/#Layouts"><strong>%s</strong></a>'.__(' - everything you need to know about using Layouts', 'ddl-layouts'),__('User Guides', 'ddl-layouts')); ?></li>
        <li><?php printf('<a target="_blank" href="http://wp-types.com/forums/forum/support-2/"><strong>%s</strong></a>'.__(' - online help by support staff', 'ddl-layouts'),__('Support forum', 'ddl-layouts') ); ?></li>
    </ul>
    <h3 style="margin-top:2em;"><?php _e('Debug information', 'ddl-layouts'); ?></h3>
    <p><?php
    printf(
    __( 'For retrieving debug information if asked by a support person, use the <a href="%s">debug information</a> page.', 'ddl-layouts' ),
    admin_url('admin.php?page=dd_layouts_debug')
    );
?></p>
    <h3 style="margin-top:2em;"><?php _e('Troubleshoot', 'ddl-layouts'); ?></h3>
	<p><?php
    printf(
    __( 'For solving known issues, use the <a href="%s">troubleshoot</a> page.', 'ddl-layouts' ),
    admin_url('admin.php?page=dd_layouts_troubleshoot')
    );
?></p>

</div>
