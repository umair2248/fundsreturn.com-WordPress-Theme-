<?php if ( post_password_required() ) { return; } ?>
<section class="comments-area"><h2><?php esc_html_e( 'Discussion', 'confidential-fund-recovery' ); ?></h2><?php if ( have_comments() ) : ?><ol class="comment-list"><?php wp_list_comments( array( 'style' => 'ol', 'short_ping' => true ) ); ?></ol><?php the_comments_pagination(); endif; comment_form(); ?></section>
