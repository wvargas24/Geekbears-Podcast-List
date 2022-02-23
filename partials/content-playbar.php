<div id="<?php echo 'podcast-playbar-'.get_the_ID(); ?>" class="playbarbox">
    <div class="playbar-post-image-bg">
        <a href="<?php the_permalink();?>" title="<?php the_title(); ?>">
            <?php the_post_thumbnail( 'full' ); ?>                    
        </a>
    </div>
    <div class="content-playbar">        
        <div class="cont-left">
            <h2 class="playbar-post-title">
                <a href="<?php the_permalink();?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
            </h2> 
            <p class="playbar-date"><?php echo get_the_date('F j, Y', get_the_ID()); ?></p>
        </div>   
        <div class="cont-right">
            <button id="mute-unmute" class="sound"></button>
            <input id="volumeslider" type="range" min="0" max="100" value="100" step="1" class="slider">
            <button id="backward" class="backward"><i class="fa fa-backward" aria-hidden="true"></i></button>
            <button id="forward" class="forward"><i class="fa fa-forward" aria-hidden="true"></i></button>
            <button id="close-playbar" class="close"><i class="fa fa-times" aria-hidden="true"></i></button>
        </div>
        <div class="box-episode-playbar" id="boxplaybar-<?php echo get_the_ID(); ?>">
            <!-- <video class="fm-video video-js vjs-16-9 vjs-big-play-centered" data-setup="{}" controls id="fm-video" >
                <source src="<?php echo $url; ?>" type="audio/mp3">
            </video> --> 
            <video src="<?php echo $url; ?>" class="video" controls id="videoplaybar"></video>
            <div class="controls">
                <div class="buttons">
                    <button id="play-pause" class="btnPlay"></button>
                </div> 
                <input id="seekslider" type="range" min="0" max="100" value="0" step="0.00001" class="seekslider">              
                <div class="progressTime">
                    <span class="current">00:00</span>/<span class="duration"><?php echo get_post_meta(get_the_ID(), 'duration', true); ?></span>
                </div>                
            </div>
        </div>
    </div>    
</div>