<div id="podcast-<?php echo get_the_ID(); ?>" class="content-episode">
	<div class="img-episode">
		<?php the_post_thumbnail( 'full' ); ?>
	</div>
	<div class="cont-episode">
		<h2><?php the_title(); ?></h2>			
		<p><?php echo $track['title']; ?></p>
	</div>
	<div class="foot-episode">
		<div class="foot-episode-date"><?php echo get_the_date('F j, Y', get_the_ID()); ?></div>
		

		<?php  
			// Get existing enclosure
			$enclosure = get_post_meta( get_the_ID(), 'enclosure', true );
			
		?>
		<div class="foot-episode-play">
			<a title="DOWNLOAD" class="ssp-playlist-caption" href="<?php echo $url; ?>" data-ssp-title="<?php echo $episode->post_title; ?>" data-ssp-series="<?php echo $episode_series; ?>" data-ssp-download="<?php echo $ss_podcasting->get_episode_download_link( $episode->ID, 'download' ); ?>">
                <i class="fa fa-download"></i>
            </a>
		</div>
		<div class="foot-episode-playbar">
				<video class="fm-video video-js vjs-16-9 vjs-big-play-centered" data-setup="{}" controls id="fm-video">
					<source src="<?php echo $url; ?>" type="<?php echo $atts['type']; ?>/mp3">
				</video>
		</div>
	</div>			
</div>