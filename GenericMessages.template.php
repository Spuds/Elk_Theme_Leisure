<?php

/**
 * @name      ElkArte Forum
 * @copyright ElkArte Forum contributors
 * @license   BSD http://opensource.org/licenses/BSD-3-Clause
 *
 * This file contains code covered by:
 * copyright:	2011 Simple Machines (http://www.simplemachines.org)
 * license:  	BSD, See included LICENSE.TXT for terms and conditions.
 *
 * @version 1.1.9
 *
 */

/**
 * Builds the poster area, avatar, group icons, pulldown information menu, etc
 *
 * @param mixed[] $message
 * @param boolean $ignoring
 *
 * @return string
 */
function template_build_poster_div($message, $ignoring = false)
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	$poster_div = '';

	// Show information about the poster of this message.
	$poster_div .= '
							<li class="' . ($ignoring ? 'subsections"' : 'listlevel1 "') . ' aria-haspopup="true">';

	// Show a link to the member's profile.
	if (!empty($settings['show_user_images']) && empty($options['show_no_avatars']) && empty($options['hide_poster_area']) && !empty($message['member']['avatar']['image']))
	{
		$poster_div .= ' 		
 							<div class="poster_avatar">
								<a class="linklevel1" ' . (!empty($message['member']['id'])
									? 'href="' . $scripturl . '?action=profile;u=' . $message['member']['id'] . '"'
									: '') . '>
									<span class="poster_area_avatar">' . $message['member']['avatar']['image'] . '</span>
								</a>
							</div>';
	}
	else
	{
		if (!empty($message['member']['id']))
			$poster_div .= '
								<a class="linklevel1 name" href="' . $message['member']['href'] . '">
									' . $message['member']['name'] . '
								</a>';
		else
			$poster_div .= '
								<a class="centertext name">
									' . $message['member']['name'] . '
								</a>';
	}


	// The new member info dropdown starts here. Note that conditionals have not been fully checked yet.
	$poster_div .= '
								<ul class="menulevel2 poster_div" id="msg_' . $message['id'] . '_extra_info"' . ($ignoring ? ' style="display:none;"' : ' aria-haspopup="true"') . '>';

	// Don't show these things for guests.
	if (!$message['member']['is_guest'])
	{
		$poster_div .= '		<li>
								<div class="poster_div_avatar">
									<img src="' . $message['member']['avatar']['href'] . '" alt="avatar" loading="lazy" />
								</div>
								<div class="poster_div_details">
								<ul>';

		// Show the post group if and only if they have no other group or the option is on, and they are in a post group.
		if ((empty($settings['hide_post_group']) || $message['member']['group'] == '') && $message['member']['post_group'] != '')
			$poster_div .= '
									<li class="listlevel2 postgroup"><strong>' . $message['member']['name'] . '</strong> / "' . $message['member']['post_group'] . '"</li>';
		else
			$poster_div .= '
									<li class="listlevel2"><strong>' . $message['member']['name'] . '</strong></li>';

		$poster_div .= '
									<li class="listlevel2">Member Since: ' . standardTime($message['member']['registered_timestamp'], '%b-%d %Y') . '</li>
									<li class="listlevel2">Last Seen: ' . $message['member']['last_login'] . '</li>';

		// Is karma display enabled?  Total or +/-?
		if (!empty($modSettings['karmaMode']))
		{
			if ($modSettings['karmaMode'] == '1')
				$poster_div .= '
									<li class="listlevel2 karma">' . $modSettings['karmaLabel'] . ' ' . ($message['member']['karma']['good'] - $message['member']['karma']['bad']);
			elseif ($modSettings['karmaMode'] == '2')
				$poster_div .= '
									<li class="listlevel2 karma">' . $modSettings['karmaLabel'] . ' +' . $message['member']['karma']['good'] . '/-' . $message['member']['karma']['bad'];
			// Is this user allowed to modify this member's karma?
			if (!empty($message['member']['karma']['allow']))
				$poster_div .= '
									<span class="karma_allow">&nbsp;
										<a class="linkbutton" href="' . $message['member']['karma']['applaud_url'] . '">' . $modSettings['karmaApplaudLabel'] . '</a>' .
										(empty($modSettings['karmaDisableSmite']) ? '&nbsp;<a class="linkbutton" href="' . $message['member']['karma']['smite_url'] . '">' . $modSettings['karmaSmiteLabel'] . '</a>' : '') . '
									</span>';

			$poster_div .= '</li>';
		}

		// Show the member's gender icon?
		if (!empty($settings['show_gender']) && $message['member']['gender']['image'] != '' && !isset($context['disabled_fields']['gender']))
			$poster_div .= '
									<li class="listlevel2 gender">' . $txt['gender'] . ': ' . $message['member']['gender']['image'] . '</li>';

		// Show their personal text?
		if (!empty($settings['show_blurb']) && !empty($message['member']['blurb']))
			$poster_div .= '
									<li class="listlevel2 blurb">' . $message['member']['blurb'] . '</li>';

		// Any custom fields to show as icons?
		if (!empty($message['member']['custom_fields']))
		{
			$shown = false;
			foreach ($message['member']['custom_fields'] as $custom)
			{
				if ($custom['placement'] != 1 || empty($custom['value']))
					continue;

				if (empty($shown))
				{
					$shown = true;
					$poster_div .= '
									<li class="listlevel2 cf_icons">
										<ol>';
				}

				$poster_div .= '
											<li class="cf_icon">' . $custom['value'] . '</li>';
			}

			if ($shown)
				$poster_div .= '
										</ol>
									</li>';
		}

		// Show the website and email address buttons.
		if ($message['member']['show_profile_buttons'])
		{
			$poster_div .= '
									<li class="listlevel2 profile">
										<ol>';

			// Don't show an icon if they haven't specified a website.
			if ($message['member']['website']['url'] != '' && !isset($context['disabled_fields']['website']))
				$poster_div .= '
											<li class="cf_icon">
												<a href="' . $message['member']['website']['url'] . '" title="' . $message['member']['website']['title'] . '" target="_blank" rel="noopener noreferrer" class="new_win">' . ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/profile/www_sm.png" alt="' . $message['member']['website']['title'] . '" />' : $txt['www']) . '</a>
											</li>';

			// Don't show the email address if they want it hidden.
			if (in_array($message['member']['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')) && $context['can_send_email'])
				$poster_div .= '
											<li>
												<a href="' . $scripturl . '?action=emailuser;sa=email;msg=' . $message['id'] . '" rel="nofollow">' . ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/profile/email_sm.png" alt="' . $txt['email'] . '" title="' . $txt['email'] . '" />' : $txt['email']) . '</a>
											</li>';

			$poster_div .= '
										</ol>
									</li>';
		}

		// Any custom fields for standard placement?
		if (!empty($message['member']['custom_fields']))
		{
			foreach ($message['member']['custom_fields'] as $custom)
			{
				if (empty($custom['placement']) || empty($custom['value']))
					$poster_div .= '
									<li class="listlevel2 custom">' . $custom['title'] . ': ' . $custom['value'] . '</li>';
			}
		}
	}
	// Otherwise, show the guest's email.
	elseif (!empty($message['member']['email']) && in_array($message['member']['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')) && $context['can_send_email'])
		$poster_div .= '
									<li class="listlevel2 email">
										<a class="linklevel2" href="' . $scripturl . '?action=emailuser;sa=email;msg=' . $message['id'] . '" rel="nofollow">' . ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/profile/email_sm.png" alt="' . $txt['email'] . '" title="' . $txt['email'] . '" />' : $txt['email']) . '</a>
									</li>';

	// Stuff for the staff to wallop them with.
	$poster_div .= '
									<li class="listlevel2 report_seperator"></li>';

	// Can we issue a warning because of this post?  Remember, we can't give guests warnings.
	if ($context['can_issue_warning'] && !$message['is_message_author'] && !$message['member']['is_guest'])
	{
		$poster_div .= '
									<li class="listlevel2 warning">
										<a class="linklevel2" href="' . $scripturl . '?action=profile;area=issuewarning;u=' . $message['member']['id'] . ';msg=' . $message['id'] . '"><img src="' . $settings['images_url'] . '/profile/warn.png" alt="' . $txt['issue_warning_post'] . '" title="' . $txt['issue_warning_post'] . '" />' . $txt['warning_issue'] . '</a>';

		// Do they have a warning in place?
		if ($message['member']['can_see_warning'] && !empty($options['hide_poster_area']))
			$poster_div .= '
										<a class="linklevel2" href="' . $scripturl . '?action=profile;area=issuewarning;u=' . $message['member']['id'] . '"><img src="' . $settings['images_url'] . '/profile/warning_' . $message['member']['warning_status'] . '.png" alt="' . $txt['user_warn_' . $message['member']['warning_status']] . '" /><span class="warn_' . $message['member']['warning_status'] . '">' . $txt['warn_' . $message['member']['warning_status']] . '</span></a>';

		$poster_div .= '
									</li>';
	}

	// Show the IP to this user for this post - because you can moderate?
	if (!empty($context['can_moderate_forum']) && !empty($message['member']['ip']))
		$poster_div .= '
									<li class="listlevel2 poster_ip">
												<a class="linklevel2 help" title="' . $message['member']['ip'] . '" href="' . $scripturl . '?action=' . (!empty($message['member']['is_guest']) ? 'trackip' : 'profile;area=history;sa=ip;u=' . $message['member']['id'] . ';searchip=' . $message['member']['ip']) . '">' . $message['member']['ip'] . '</a>
												<a class="helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=see_admin_ip" onclick="return reqOverlayDiv(this.href);"></a>
									</li>';
	// Or, should we show it because this is you?
	elseif ($message['can_see_ip'] && !empty($message['member']['ip']))
		$poster_div .= '
									<li class="listlevel2 poster_ip">
												<a class="linklevel2 help" title="' . $message['member']['ip'] . '" href="#" onclick="return false;">' . $message['member']['ip'] . '</a>
												<a class="linklevel2 helpicon i-help"  title="' . $message['member']['ip'] . '" href="' . $scripturl . '?action=quickhelp;help=see_member_ip" onclick="return reqOverlayDiv(this.href);"><s>' . $txt['help'] . '</s></a>
									</li>';
	// Okay, are you at least logged in?  Then we can show something about why IPs are logged...
	elseif (!$context['user']['is_guest'])
		$poster_div .= '
									<li class="listlevel2 poster_ip">
												<a class="linklevel2 helpicon i-help" href="' . $scripturl . '?action=quickhelp;help=see_member_ip" onclick="return reqOverlayDiv(this.href);"><s>' . $txt['help'] . '</s></a>' . $txt['logged'] . '
									</li>';
	// Otherwise, you see NOTHING!
	else
		$poster_div .= '
									<li class="listlevel2 poster_ip">' . $txt['logged'] . '</li>';

	// Done with the detail flyout information about the poster.
	if (!$message['member']['is_guest'])
	$poster_div .= '

								</ul>
							</div>
						</li>
					</ul>
				</li>';
	else
		$poster_div .= '
							</ul>
						</li>';


	if (!empty($settings['show_user_images']) && empty($options['show_no_avatars']) && !empty($message['member']['avatar']['image']) && empty($options['hide_poster_area']))
	{
		if (!empty($message['member']['id']))
		{
			$poster_div .= '
						<li class="listlevel1 name">
							<a href="' . $scripturl . '?action=profile;u=' . $message['member']['id'] . '">' . $message['member']['name'] . '</a>
						</li>';
		}
		else
		{
			$poster_div .= '
								<li class="listlevel1 name">
									' . $message['member']['name'] . '
								</li>';
		}
	}

	// Show avatars, images, etc.?
	if (empty($options['hide_poster_area']) && !$ignoring)
	{
		// Show the post group icons, but not for guests.
		if (!$message['member']['is_guest'])
			$poster_div .= '
							<li class="listlevel1 icons">' . $message['member']['group_icons'] . '</li>';

		// Show the member's primary group (like 'Administrator') if they have one.
		if (!empty($message['member']['group']))
			$poster_div .= '
							<li class="listlevel1 membergroup">' . $message['member']['group'] . '</li>';
		elseif ((empty($settings['hide_post_group']) || $message['member']['group'] == '') && $message['member']['post_group'] != '')
			$poster_div .= '
							<li class="listlevel1 membergroup">' . $message['member']['post_group'] . '</li>';

		// Show the member's custom title, if they have one.
		if (!empty($message['member']['title']))
			$poster_div .= '
							<li class="listlevel1 title">' . $message['member']['title'] . '</li>';

		// Are we showing the warning status?
		if (!$message['member']['is_guest'] && $message['member']['can_see_warning'])
			$poster_div .= '
							<li class="listlevel1 warning">' . ($context['can_issue_warning'] ? '<a class="linklevel1" href="' . $scripturl . '?action=profile;area=issuewarning;u=' . $message['member']['id'] . '">' : '') . '<img src="' . $settings['images_url'] . '/profile/warning_' . $message['member']['warning_status'] . '.png" alt="' . $txt['user_warn_' . $message['member']['warning_status']] . '" />' . ($context['can_issue_warning'] ? '</a>' : '') . '<span class="warn_' . $message['member']['warning_status'] . '">' . $txt['warn_' . $message['member']['warning_status']] . '</span></li>';

		$poster_div .= '
							<li><span class="pointer"></span></li>';

		// The billboard below the main poster area
		if (!$message['member']['is_guest'])
		{
			$poster_div .= '
						</ul>
						<ul class="poster_details">';

			// we start with their own..
			if ($context['can_send_pm'] && $message['is_message_author'])
			{
				$poster_div .= '
							<li class="billboard poster_online">
								<a class="' . ($context['user']['unread_messages'] > 0 ? 'infotext' : '') . '" href="' . $scripturl . '?action=pm">
									<i class="fa fa-comments-o fa-2x"></i>
									<p>' . ($context['user']['unread_messages'] > 0 ? $context['user']['unread_messages'] : '0') . '</p>
								</a>
							</li>';
			}
			// Allowed to send PMs and the message is not their own and not from a guest.
			elseif ($context['can_send_pm'] && !$message['is_message_author'] && !$message['member']['is_guest'])
			{
				if (!empty($modSettings['onlineEnable']))
					$poster_div .= '
							<li class="billboard poster_online">
								<a href="' . $scripturl . '?action=pm;sa=send;u=' . $message['member']['id'] . '" title="' . $message['member']['online']['member_online_text'] . '"><i class="fa fa-comments-o fa-2x ' . (!empty($message['member']['online']['is_online']) ? 'successtext' : 'stdtext') . '"></i></a>
							</li>';
				else
					$poster_div .= '
							<li class="billboard poster_online">
								<a href="' . $scripturl . '?action=pm;sa=send;u=' . $message['member']['id'] . '" title="' . $txt['send_message'] . '"><i class="fa fa-comments-o fa-2x"></i></a>
							</li>';
			}
			// Not allowed to send a PM, online status disabled and not from a guest.
			elseif (!$context['can_send_pm'] && !empty($modSettings['onlineEnable']) && !$message['member']['is_guest'])
				$poster_div .= '
							<li class="billboard poster_online">
								<i class="fa fa-comments-o fa-2x '. (!empty($message['member']['online']['is_online']) ? 'successtext' : 'stdtext') . '" title="' . ($message['member']['online']['is_online'] ? $txt['online'] : $txt['offline']) . '"></i>
							</li>';

			// Show how many posts they have made.
			if (!isset($context['disabled_fields']['posts']))
				$poster_div .= '
							<li class="billboard postcount">
								<i class="fa fa-stack-overflow fa-2x" title="' . $txt['member_postcount'] . '"></i>
								<p>' . $message['member']['posts'] . '</p>
							</li>';

			if (!isset($context['disabled_fields']['likes']))
				$poster_div .= '
							<li class="billboard likecount">
								<i class="fa fa-thumbs-o-up fa-2x" title="' . $txt['likes'] . '"></i>
								<p>' . $message['member']['likes']['received'] . '</p>
							</li>';
		}
	}

	return $poster_div;
}

/**
 * Formats a very simple message view (for example search results, list of
 * posts and topics in profile, unapproved, etc.)
 *
 * @param mixed[] $msg associative array containing the data to output:
 * - class => a class name (mandatory)
 * - counter => Usually a number used as counter next to the subject
 * - title => Usually the subject of the topic (mandatory)
 * - date => frequently the "posted on", but can be anything
 * - body => message body (mandatory)
 * - buttons => an associative array that allows create a "quickbutton" strip
 *  (see template_quickbutton_strip for details on the parameters)
 */
function template_simple_message($msg)
{
	// @todo find a better name for $msg['date']
	echo '
			<article class="', $msg['class'], ' core_posts">', !empty($msg['counter']) ? '
				<div class="counter">' . $msg['counter'] . '</div>' : '', '
				<header class="topic_details">
					<h5>
						', $msg['title'], '
					</h5>', !empty($msg['date']) ? '
					<span class="smalltext">' . $msg['date'] . '</span>' : '', '
				</header>
				<section class="inner">
					', $msg['body'], '
				</section>';

	if (!empty($msg['buttons']))
		template_quickbutton_strip($msg['buttons'], !empty($msg['tests']) ? $msg['tests'] : array());

	echo '
			</article>';
}
