<div id="podcast-<?php echo $episode->ID; ?>" class="content-episode">
	<div class="img-episode">
		<img src="<?php echo $src; ?>" >
	</div>
	<div class="cont-episode">
		<h2><?php echo $track['caption']; ?></h2>			
		<p><?php echo $track['title']; ?></p>
	</div>
	<div class="foot-episode">
		<div class="foot-episode-date"><?php echo get_the_date('F j, Y', $episode->ID); ?></div>
		<div class="foot-episode-play">
			<a title="PLAY" class="ssp-playlist-caption link-show-playbar" href="" id="link-<?php echo $episode->ID; ?>">
                <i class="fa fa-play"></i>
            </a>
            <a title="DOWNLOAD" class="ssp-playlist-caption" href="<?php echo $url; ?>" data-ssp-title="<?php echo $episode->post_title; ?>" data-ssp-series="<?php echo $episode_series; ?>" data-ssp-download="<?php echo $ss_podcasting->get_episode_download_link( $episode->ID, 'download' ); ?>" download>
                <i class="glyphicon glyphicon-download-alt"></i>
            </a>
		</div>
	</div>			
</div>