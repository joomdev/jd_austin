<?php
/**
 * @version   $Id: edit_article_info_zoo.php 10885 2013-05-30 06:31:41Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
?>

<h1 class="popover-title"><?php echo $article->title;?></h1>
<div class="article-info">
	<ul>
		<li>
			<span class="title"><?php rc_e('ROKSPROCKET_ID')?></span>
			<span class="content"><?php echo $article->id;?></span>
		</li>
		<li>
			<span class="title"><?php rc_e('ROKSPROCKET_STATUS')?></span>
			<span class="content"><?php
				switch($article->published) {
					case 1:
						$published = rc_e('PUBLISHED');
						$published .= ' <span class="green">&#9679;</span>';
						break;
					case 2:
						$published = rc_e('ARCHIVED');
						$published .= ' <span class="red">&#9679;</span>';
						break;
					case 0:
						$published = rc_e('UNPUBLISHED');
						$published .= ' <span class="red">&#9679;</span>';
						break;
					case -2: default:
						$published = rc_e('TRASHED');
						$published .= ' <span class="red">&#9679;</span>';
						break;
				}
				echo $published;
			?></span>
		</li>
		<li>
			<span class="title"><?php rc_e('ROKSPROCKET_ACCESS')?></span>
			<span class="content"><?php echo $article->access_title;?></span>
		</li>
		<li>
			<span class="title"><?php rc_e('ROKSPROCKET_ALIAS')?></span>
			<span class="content"><?php echo $article->alias;?></span>
		</li>
		<li>
			<span class="title"><?php rc_e('ROKSPROCKET_CATEGORY')?></span>
			<span class="content"><?php echo $article->category_title;?></span>
		</li>
		<li>
			<span class="title"><?php rc_e('ROKSPROCKET_FEATURED')?></span>
			<span class="content"><?php if ($article->featured) {
				$yes = rc_e('ROKSPROCKET_YES');
				$yes .= ' <span class="green">&#9679;</span>';
				echo $yes;
			} else {
				$no = rc_e('ROKSPROCKET_NO');
				$no .= ' <span class="red">&#9679;</span>';
				echo $no;
			}?></span>
		</li>
		<li>
			<span class="title"><?php rc_e('ROKSPROCKET_CREATED_BY')?></span>
			<span class="content"><?php echo $article->author_name;?></span>
		</li>
		<li>
			<span class="title"><?php rc_e('ROKSPROCKET_CREATED_DATE')?></span>
			<span class="content"><?php echo $article->created;?></span>
		</li>
		<li>
			<span class="title"><?php rc_e('ROKSPROCKET_MODIFIED_DATE')?></span>
			<span class="content"><?php echo $article->modified;?></span>
		</li>
	</ul>
	<div class="statusbar">
		<a class="btn btn-primary" href="<?php echo $article->editUrl;?>" target="_blank">Edit</a>
	</div>
</div>
