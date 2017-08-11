<?php
function emtr_template_redirect(){
	if( !empty( $_GET['emtr_email_id'] ) && intval( ( $_GET['emtr_email_id'] ) ) > 0 && !empty( $_GET['emtr_action'] ) && $_GET['emtr_action'] == 'view' ) {
		$email_id = intval( ( $_GET['emtr_email_id'] ) );
		$item = EMTR_Model::TrackEmail()->get_email_view_data( $email_id );
		
		?>
        <style>
		.click-item-cont .click-item:first-child { margin-top:5px; }
		.click-item { border:1px solid #999; background-color: #CCC; border-radius:10px; padding:14px; margin-bottom: 7px;  box-shadow: 2px 2px 2px #888888;
 }
 		.click-item .field { margin: 4px 0; }
		.click-item label { font-weight:900; font-size:16px;  width: 110px; display: inline-block; }
		</style>
        <b><?php esc_html_e('To :', EMTR_TEXT_DOMAIN);?></b>
        <br/>
        <?php echo htmlspecialchars( $item['to'] );?>
        <br/><br/>
        
        <b><?php esc_html_e('Subject :', EMTR_TEXT_DOMAIN);?></b><br/><?php echo $item['subject'];?>
        <br><br>

		<b><?php esc_html_e('Read Log :',EMTR_TEXT_DOMAIN);?></b>
        <br/> 
        <b>
			<?php echo sprintf( _n( '%s time', '%s times', $item['view_count']), $item['view_count'] );?>	<?php esc_html_e(' read',EMTR_TEXT_DOMAIN);?>				
        </b>
        <?php
				if( !empty($item['view_date_time']) ) {
					$arr_view_date_time = explode(',',$item['view_date_time']);
					rsort( $arr_view_date_time );
					foreach($arr_view_date_time as $key=>$date_time)	{
						$arr_view_date_time[$key] = get_date_from_gmt($date_time, 'F j, Y g:i A').emtr_relative_time(get_date_from_gmt( $date_time ));
					}
					 echo '<br/>'.implode( '<br/>', $arr_view_date_time );
					
				}
		?>				
				
                <br/>
                <br/>
                
          <?php 
		  //smartyt start
		  ?>
                
           <?php 
		   $rs_link_log = EMTR_Model::TrackEmail()->get_link_log( $email_id );
		  
		   if( !empty( $rs_link_log ) )
		   ?>
           
                <div class="click-block">
                    <div class="box-left">
                    	<b><?php esc_html_e('Link Click Log:', EMTR_TEXT_DOMAIN);?></b>
                    	<?php 
                        $sum_click = 0;
                        foreach( $rs_link_log as $link_log ) {
							$sum_click = $sum_click + $link_log['total_clicked'];
						}
						?>
                        <br/>
                        <b>
                        <?php echo sprintf( _n( '%s click', '%s clicks', $sum_click), $sum_click );?>                        
                        </b>
                    </div>
                    <div class="click-item-cont">                 
					<?php
                    foreach( $rs_link_log as $link_log ) {
                        ?>
                        <div class="click-item">
							<div class="field">
			              	<label>
								<?php esc_html_e('Link:', EMTR_TEXT_DOMAIN);?>
                             </label>
                                <span style="word-wrap: break-word; width: 260px; max-width: 260px;"><?php echo $link_log['trklink_link'];?></span>
							</div>                             
                            <div class="field">
                             <label>
                             <?php esc_html_e('# of clicks:',EMTR_TEXT_DOMAIN);?>
                             </label>
								<?php 
                                if( $link_log['total_clicked'] > 0) {
                                    echo $link_log['total_clicked'];
                                }
                                else {
                                    esc_html_e('Not clicked yet',EMTR_TEXT_DOMAIN);
                                }
                                ?>
                             </div>
                             <div class="field">
                                <label>
                                <?php esc_html_e('Last clicked on:', EMTR_TEXT_DOMAIN); ?>
								</label>
								<?php
                                    if( $link_log['total_clicked'] > 0) {
    
                                        echo get_date_from_gmt($link_log['last_clicked'], 'F j, Y g:i A').emtr_relative_time(get_date_from_gmt( $link_log['last_clicked'] ));
    
                                    }
                                    else {
                                         esc_html_e('Not clicked yet',EMTR_TEXT_DOMAIN);
                                    }
                                ?>
                            </div>
                        </div>
                        <?php 
                        }
                        ?>
                           
                                 
                    </div>
                    <div class="clear"></div>
                </div>
              
        </div>
    
            
            
           <?php //smaty end ?>
                <br/>
                <br/>
				
				<b><?php esc_html_e('Date :', EMTR_TEXT_DOMAIN);?></b>
                <br/>
				<?php echo get_date_from_gmt($item['date_time'] ,'F j, Y g:i A').emtr_relative_time(get_date_from_gmt( $item['date_time'] ));?>
                <br/>
                <br/>
                
				<?php
				if( !empty($item['headers']) ) {
					?>
					<b><?php esc_html_e('Headers :', EMTR_TEXT_DOMAIN);?></b><br/><?php echo nl2br( $item['headers'] );?><br><br>
                    <?php
				}
				if( !empty($item['attachments']) ) {
					$arr_attachments = explode( ',\n', $item['attachments'] );
					
					$str_attach = '';
					foreach( $arr_attachments as $key=>$attach ) {
						$str_attach .= '<a href="'.$this->content_path.$attach.'" target="_blank">'.$this->content_path.$attach.'</a>';
						if( $key != count($arr_attachments) - 1 ) {
							$str_attach .= ',<br/>';
						}
					}?>
					<b><?php esc_html_e('Attachments :',EMTR_TEXT_DOMAIN);?></b><br/><?php echo $str_attach;?><br><br>
					<?php
				}
				?>
					
			
				<b><?php esc_html_e('Message :', EMTR_TEXT_DOMAIN);?></b><br/><?php echo balanceTags($item['message']);?>
        <?php
		exit();
	}
}
 
// add our function to template_redirect hook
add_action('template_redirect', 'emtr_template_redirect');