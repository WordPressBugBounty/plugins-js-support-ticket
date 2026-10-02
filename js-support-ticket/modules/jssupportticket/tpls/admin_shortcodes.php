<?php
   if(!defined('ABSPATH'))
    die('Restricted Access');
?>
<?php
	JSSTincluder::getJSModel('jssupportticket')->jsst_get_theme_colors();
	$jsst_color1 = jssupportticket::$jsst_colors['color1'];
    $jsst_color3 = jssupportticket::$jsst_colors['color3'];
?>
<div id="jsstadmin-wrapper">
    <div id="jsstadmin-leftmenu">
        <?php  JSSTincluder::getClassesInclude('jsstadminsidemenu'); ?>
    </div>
    <div id="jsstadmin-data">
        <?php JSSTlayout::adminPageHeader(array(
            'title'   => __('Shortcodes and blocks', 'js-support-ticket'),
            'crumbs'  => array(),
            'actions' => array(
                array('text' => __('Watch Video', 'js-support-ticket'), 'url' => 'https://www.youtube.com/watch?v=mN6xsD2u2CI', 'style' => 'ghost', 'target' => '_blank'),
            ),
        )); ?>
        <div id="jsstadmin-data-wrp">
			<?php
			/* One table, both doors. (Roadmap 4.0-UX-07)
			
			   This screen used to be two: a list of shortcodes, then a list of
			   blocks, then - in the blocks card's own words - "every block below runs
			   the shortcode beside it". It was the same seven features written out
			   twice, once per way of placing them, which is twice the page for none
			   of the answer: somebody who has found the FAQs row has found both ways
			   to put FAQs on a page and does not need to meet FAQs again four
			   screens later.
			
			   So the two columns are the two doors, side by side on one row. The
			   difference between them - a block previews itself while you build the
			   page, a shortcode does not - is said once, where somebody is choosing
			   between them, instead of being the premise of a whole second section.
			
			   The blocks are read from JSSTblocks rather than listed again here, and
			   matched to a row by the shortcode each one runs. That is the same join
			   the class itself makes, so a block added there appears here with no
			   second list to remember, and a feature that never had a block says so
			   rather than showing an empty cell. */
			$jsst_blockby = array();
			if (class_exists('JSSTblocks')) {
				foreach (JSSTblocks::blocks() as $jsst_bkey => $jsst_block) {
					$jsst_blockby[$jsst_block['shortcode']] = $jsst_block;
				}
			}
			$jsst_variants = array(
				'all'     => __('Everything', 'js-support-ticket'),
				'latest'  => __('The latest', 'js-support-ticket'),
				'popular' => __('The most read', 'js-support-ticket'),
			);
			$jsst_rows = array();
			$jsst_rows[] = array(
				'title' => __('Help Desk portal', 'js-support-ticket'),
				'does'  => __('The whole customer portal: their tickets, a way to raise one, and everything the desk offers them.', 'js-support-ticket'),
				'codes' => array(array('code' => 'jssupportticket', 'note' => '')),
			);
			$jsst_rows[] = array(
				'title' => __('Raise a ticket', 'js-support-ticket'),
				'does'  => __('The form on its own, for a contact page that should do one thing.', 'js-support-ticket'),
				'codes' => array(array('code' => 'jssupportticket_addticket', 'note' => '')),
			);
			$jsst_rows[] = array(
				'title' => __('My tickets', 'js-support-ticket'),
				'does'  => __('What this customer has already asked, and where each one has got to.', 'js-support-ticket'),
				'codes' => array(array('code' => 'jssupportticket_mytickets', 'note' => '')),
			);
			if (in_array('multiform', jssupportticket::$_active_addons)) {
				/* One line per form rather than one row per form: they are the same
				   shortcode with a different id, and the id is the only thing the
				   reader has to copy correctly. */
				$jsst_codes = array();
				$jsst_multiforms = isset(jssupportticket::$jsst_data[0]['multiforms'])
					? jssupportticket::$jsst_data[0]['multiforms'] : array();
				foreach ((array) $jsst_multiforms as $jsst_multiform) {
					$jsst_note = $jsst_multiform->title;
					if (isset($jsst_multiform->departmentname) && $jsst_multiform->departmentname !== '') {
						$jsst_note .= ' — ' . $jsst_multiform->departmentname;
					}
					$jsst_codes[] = array(
						'code' => 'jssupportticket_addticket_multiform formid=' . (int) $jsst_multiform->id,
						'note' => $jsst_note,
					);
				}
				if (!empty($jsst_codes)) {
					$jsst_rows[] = array(
						'title' => __('Raise a ticket, on one of your forms', 'js-support-ticket'),
						'does'  => __('The same form built to your own questions. Each one has its own shortcode.', 'js-support-ticket'),
						'codes' => $jsst_codes,
					);
				}
			}
			/* The four that come in all/latest/popular. The block for one of these
			   is a single block with the list chosen in its own settings panel, not
			   three blocks, which is why the shortcode column has three lines here
			   and the block column has one. */
			$jsst_listing = array(
				array('addon' => 'download', 'stem' => 'jssupportticket_downloads',
					'title' => __('Downloads', 'js-support-ticket'),
					'does'  => __('Files customers may fetch for themselves.', 'js-support-ticket')),
				array('addon' => 'knowledgebase', 'stem' => 'jssupportticket_knowledgebase',
					'title' => __('Knowledge Base', 'js-support-ticket'),
					'does'  => __('Articles customers can search before they write in.', 'js-support-ticket')),
				array('addon' => 'faq', 'stem' => 'jssupportticket_faqs',
					'title' => __('FAQs', 'js-support-ticket'),
					'does'  => __('The questions you are asked most often.', 'js-support-ticket')),
				array('addon' => 'announcement', 'stem' => 'jssupportticket_announcements',
					'title' => __('Announcements', 'js-support-ticket'),
					'does'  => __('What you want every customer to see before they ask.', 'js-support-ticket')),
			);
			foreach ($jsst_listing as $jsst_one) {
				if (!in_array($jsst_one['addon'], jssupportticket::$_active_addons)) {
					continue;
				}
				$jsst_rows[] = array(
					'title' => $jsst_one['title'],
					'does'  => $jsst_one['does'],
					'codes' => array(
						array('code' => $jsst_one['stem'], 'note' => $jsst_variants['all']),
						array('code' => $jsst_one['stem'] . '_latest', 'note' => $jsst_variants['latest']),
						array('code' => $jsst_one['stem'] . '_popular', 'note' => $jsst_variants['popular']),
					),
				);
			}
			?>
			<p class="jsst-lede"><?php echo esc_html(__('Every part of the help desk you can put on a page of your own, and the two ways to put it there. Only the features that are on this site are listed, so everything here works as soon as you use it.', 'js-support-ticket')); ?></p>
			<div class="jsst-card">
				<div class="jsst-card-head">
					<h2 class="jsst-card-title"><?php echo esc_html(__('Everything you can put on a page', 'js-support-ticket')); ?></h2>
					<p class="jsst-card-sub"><?php echo esc_html(__('The shortcode and the block are the same feature, and neither is the newer one you should be using — the block simply previews itself while you build the page. Nothing you have already built needs changing.', 'js-support-ticket')); ?></p>
				</div>
				<div class="jsst-card-body jsst-card-flush">
					<div class="jsst-table-wrap">
						<table class="jsst-table jsst-sc-table">
							<thead>
								<tr>
									<th scope="col"><?php echo esc_html(__('What it puts on the page', 'js-support-ticket')); ?></th>
									<th scope="col"><?php echo esc_html(__('Paste this shortcode', 'js-support-ticket')); ?></th>
									<?php if (!empty($jsst_blockby)) { ?>
										<th scope="col"><?php echo esc_html(__('Or add this block', 'js-support-ticket')); ?></th>
									<?php } ?>
								</tr>
							</thead>
							<tbody>
							<?php foreach ($jsst_rows AS $jsst_row) {
								$jsst_first = $jsst_row['codes'][0]['code'];
								$jsst_block = isset($jsst_blockby[$jsst_first]) ? $jsst_blockby[$jsst_first] : null; ?>
								<tr>
									<th scope="row">
										<span class="jsst-table-name"><?php echo esc_html($jsst_row['title']); ?></span>
										<span class="jsst-table-sub"><?php echo esc_html($jsst_row['does']); ?></span>
									</th>
									<td>
									<?php foreach ($jsst_row['codes'] AS $jsst_code) {
										$jsst_full = '[' . $jsst_code['code'] . ']'; ?>
										<div class="jsst-sc-code">
											<code><?php echo esc_html($jsst_full); ?></code>
											<?php /* The one thing anybody comes to this page to do. It
											         copies from the attribute rather than from the
											         element, so a line the browser has wrapped is still
											         copied whole and without the label beside it. */ ?>
											<button type="button" class="button-link jsst-sc-copy" data-jsst-copy="<?php echo esc_attr($jsst_full); ?>"><?php
												echo esc_html(__('Copy', 'js-support-ticket')); ?></button>
											<?php if ($jsst_code['note'] !== '') { ?>
												<span class="jsst-sc-note"><?php echo esc_html($jsst_code['note']); ?></span>
											<?php } ?>
										</div>
									<?php } ?>
									</td>
									<?php if (!empty($jsst_blockby)) { ?>
										<td>
										<?php if ($jsst_block === null) { ?>
											<?php /* Said, not left blank. An empty cell in a column of
											         names reads as something that failed to load. */ ?>
											<span class="jsst-sc-none"><?php echo esc_html(__('Shortcode only', 'js-support-ticket')); ?></span>
										<?php } else { ?>
											<span class="jsst-sc-block"><?php echo esc_html($jsst_block['title']); ?></span>
											<?php if (count($jsst_row['codes']) > 1 && !empty($jsst_block['variants'])) { ?>
												<span class="jsst-table-sub"><?php echo esc_html(__('One block; pick which list in its settings.', 'js-support-ticket')); ?></span>
											<?php } ?>
										<?php } ?>
										</td>
									<?php } ?>
								</tr>
							<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
				<div class="jsst-card-foot">
					<?php /* Said once, here, rather than after each of the twelve listing
					         shortcodes it applies to. The example uses this desk's own two
					         colours, so it is a line somebody can paste and see. */ ?>
					<p class="jsst-fhelp"><?php
						/* No sentence punctuation directly after a chip: the chip carries
						   its own padding, so a full stop lands a space clear of the word it
						   belongs to and reads as a stray mark. Dashes have room around them
						   anyway. */
						echo esc_html(__('The latest and most-read lists take two colours', 'js-support-ticket'));
						echo ' — <code>text_color</code> ' . esc_html(__('and', 'js-support-ticket')) . ' <code>background_color</code> — ';
						echo esc_html(__('for example', 'js-support-ticket'));
						echo ' <code>[jssupportticket_faqs_latest text_color=&quot;' . esc_html($jsst_color3) . '&quot; background_color=&quot;' . esc_html($jsst_color1) . '&quot;]</code>'; ?></p>
					<?php if (!empty($jsst_blockby)) { ?>
						<p class="jsst-fhelp"><?php echo esc_html(__('The blocks are in the editor\'s inserter, under this plugin\'s own heading.', 'js-support-ticket')); ?></p>
					<?php } ?>
				</div>
			</div>

			<?php
			/* The old sidebar widgets. Reported rather than migrated: where a widget
			   sits is a decision about somebody's theme, and this plugin cannot see
			   their page. Kept on this screen rather than given one of its own,
			   because this is the page somebody already opens to answer "how do I put
			   the help desk on a page" - and three ways of doing that described on
			   three screens is how a simple question gets hard. (Roadmap 4.0-UX-07) */
			if (class_exists('JSSTwidgetretirement') && JSSTwidgetretirement::available()) {
				$jsst_widgets = JSSTwidgetretirement::survey(); ?>
				<h2 class="jsst-groupheading"><?php echo esc_html(__('The old sidebar widgets', 'js-support-ticket')); ?></h2>
				<div class="jsst-card">
					<div class="jsst-card-head">
						<h2 class="jsst-card-title"><?php echo esc_html(__('What you have, and what replaces it', 'js-support-ticket')); ?></h2>
						<p class="jsst-card-sub"><?php echo esc_html(__('Nothing here is moved or removed for you, and that is deliberate: where a widget sits is a decision about your theme — which sidebar, in what order, next to what — and this plugin cannot see your page. Your widgets keep working until you replace them yourself.', 'js-support-ticket')); ?></p>
					</div>
					<div class="jsst-card-body jsst-card-flush">
						<div class="jsst-table-wrap">
							<table class="jsst-table jsst-widget-table">
								<thead>
									<tr>
										<th scope="col"><?php echo esc_html(__('Widget', 'js-support-ticket')); ?></th>
										<th scope="col"><?php echo esc_html(__('Where it is', 'js-support-ticket')); ?></th>
										<th scope="col"><?php echo esc_html(__('What to use instead', 'js-support-ticket')); ?></th>
									</tr>
								</thead>
								<tbody>
								<?php foreach ($jsst_widgets['widgets'] AS $jsst_class => $jsst_widget) { ?>
									<tr>
										<th scope="row">
											<span class="jsst-table-name"><?php echo esc_html($jsst_widget['label']); ?></span>
											<span class="jsst-table-sub"><?php echo esc_html($jsst_widget['does']); ?></span>
										</th>
										<td>
											<?php if (empty($jsst_widget['instances'])) { ?>
												<span class="jsst-pill jsst-pill-off"><span class="jsst-dot"></span><?php echo esc_html(__('Not in use', 'js-support-ticket')); ?></span>
											<?php } else {
												foreach ($jsst_widget['instances'] AS $jsst_instance) { ?>
													<div>
														<span class="jsst-pill <?php echo $jsst_instance['live'] ? 'jsst-pill-warn' : 'jsst-pill-off'; ?>"><span class="jsst-dot"></span><?php
															echo esc_html($jsst_instance['live'] ? __('Live', 'js-support-ticket') : __('Not shown', 'js-support-ticket')); ?></span>
														<span class="jsst-table-sub"><?php
															echo esc_html($jsst_instance['title'] !== '' ? $jsst_instance['title'] : $jsst_instance['id']);
															echo ' — ' . esc_html(JSSTwidgetretirement::sidebarName($jsst_instance['sidebar']));
														?></span>
													</div>
												<?php }
											} ?>
										</td>
										<td>
											<?php echo esc_html($jsst_widget['replacement']); ?>
											<?php if ($jsst_widget['shortcode'] !== '') {
												$jsst_full = '[' . $jsst_widget['shortcode'] . ']'; ?>
												<div class="jsst-sc-code">
													<code><?php echo esc_html($jsst_full); ?></code>
													<button type="button" class="button-link jsst-sc-copy" data-jsst-copy="<?php echo esc_attr($jsst_full); ?>"><?php
														echo esc_html(__('Copy', 'js-support-ticket')); ?></button>
												</div>
											<?php } ?>
										</td>
									</tr>
								<?php } ?>
								</tbody>
							</table>
						</div>
					</div>
					<div class="jsst-card-foot">
						<p class="jsst-fhelp"><?php echo esc_html(__('A block can go in a sidebar too — WordPress widget areas take blocks — so replacing one of these is usually a matter of adding the block where the widget is and removing the widget.', 'js-support-ticket')); ?></p>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
</div>

<?php /* Copy, without jQuery and without a library. (Roadmap 4.0-UX-07)

         One delegated listener on the document rather than one per button, so
         it costs the same whether the page lists four shortcodes or forty.

         `navigator.clipboard` is refused outside a secure context, and plenty
         of these desks are administered over plain http on a local network, so
         the fallback below is not legacy support - it is the path that actually
         runs for some of the people reading this page. The button says what
         happened either way, because a copy button that looks identical before
         and after the click leaves somebody pasting to find out. */ ?>
<script>
(function () {
	function jsstFlash(jsst_button, jsst_text) {
		var jsst_was = jsst_button.getAttribute('data-jsst-was') || jsst_button.textContent;
		jsst_button.setAttribute('data-jsst-was', jsst_was);
		jsst_button.textContent = jsst_text;
		jsst_button.classList.add('jsst-sc-copied');
		window.setTimeout(function () {
			jsst_button.textContent = jsst_was;
			jsst_button.classList.remove('jsst-sc-copied');
		}, 1600);
	}
	/* The shortcode itself is selected, on the page, rather than a textarea
	   parked off-screen. Both end in execCommand, which is deprecated and may
	   simply refuse - and when it refuses, an off-screen textarea leaves the
	   reader looking at a button that says "Press Ctrl+C" with nothing selected
	   to press it on. Selecting what they can see means the instruction is true
	   whether or not the copy went through. */
	function jsstFallback(jsst_button) {
		var jsst_code = jsst_button.parentNode ? jsst_button.parentNode.querySelector('code') : null;
		if (!jsst_code) {
			return false;
		}
		var jsst_range = document.createRange();
		jsst_range.selectNodeContents(jsst_code);
		var jsst_selection = window.getSelection();
		jsst_selection.removeAllRanges();
		jsst_selection.addRange(jsst_range);
		var jsst_done = false;
		try { jsst_done = document.execCommand('copy'); } catch (jsst_e) { jsst_done = false; }
		if (jsst_done) {
			jsst_selection.removeAllRanges();
		}
		return jsst_done;
	}
	document.addEventListener('click', function (jsst_event) {
		var jsst_button = jsst_event.target.closest ? jsst_event.target.closest('.jsst-sc-copy') : null;
		if (!jsst_button) {
			return;
		}
		jsst_event.preventDefault();
		var jsst_value = jsst_button.getAttribute('data-jsst-copy') || '';
		var jsst_ok = <?php echo wp_json_encode(__('Copied', 'js-support-ticket')); ?>;
		var jsst_no = <?php echo wp_json_encode(__('Press Ctrl+C', 'js-support-ticket')); ?>;
		if (navigator.clipboard && window.isSecureContext) {
			navigator.clipboard.writeText(jsst_value).then(function () {
				jsstFlash(jsst_button, jsst_ok);
			}, function () {
				jsstFlash(jsst_button, jsstFallback(jsst_button) ? jsst_ok : jsst_no);
			});
			return;
		}
		jsstFlash(jsst_button, jsstFallback(jsst_button) ? jsst_ok : jsst_no);
	});
})();
</script>
