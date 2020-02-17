<div class="wrap">
  <div id="tabs">

  <style>
    #nav-tabs { overflow: hidden; margin: 0 0 -1px 0;}
    #nav-tabs li { float: left; margin-bottom: 0;}
    .ui-tabs-nav a { color: #aaa;}
    #nav-tabs li.ui-state-active a { border-bottom: 2px solid white; color: #464646; }
    h2.nav-tab-wrapper { margin-bottom: 1em;}
  </style>

  <h2><?php _e('Featured Items Metabox',"featured-items-metabox");?></h2>

  <!-- Beginning of the Plugin Options Form -->
  <form method="post" action="options.php">
    <?php settings_fields('featured_items_metabox_options'); ?>
    <?php $options = get_option('featured_items_metabox_options', false);  ?>

    <div id="general">
        <fieldset>
              <table class="form-table">
                    <tr>
                      <th scope="row"><?php _e('Select Post Types');?></th>
                      <td>

                        <?php

                        $types = get_post_types( array( 'public' => true ), 'objects' );
                        ksort( $types );


                        if( ! is_wp_error( $types ) ) {

                          foreach ($types as $i=>$type)  { ?>
                            <input type="checkbox" name="featured_items_metabox_options[types][]" value="<?php echo $i;?>" <?php checked( isset($options['types']) && is_array($options['types']) && in_array($i, $options['types']), 1 ); ?> /> <?php echo $type->labels->name; ?><br/>

                          <?php
                              }

                        } ?>

                      </td>
                    </tr>
                    <tr>
                      <th scope="row"><?php _e('Completely remove options on plugin removal');?></th>
                      <td>
                        <input type="checkbox" name="featured_items_metabox_options[delete]" value="1" <?php checked( isset( $options['delete'] ) && $options['delete'], 1 );?> />
                      </td>
                    </tr>
                  </table>
          </fieldset>
      </div>

          <p class="submit">
                <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
          </p>
    </form>
  </div>
</div>