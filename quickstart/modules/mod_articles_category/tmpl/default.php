<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_category
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<div class="article-list category-module<?php echo $moduleclass_sfx; ?>">
	<?php if ($grouped) : ?>
		<?php foreach ($list as $group_name => $group) : ?>
		<div class="article mod-articles-category-group">
			<div class="mod-articles-category-group-title"><?php echo $group_name; ?></div>
				<?php foreach ($group as $item) : $image = json_decode($item->images); ?>
					<div class="category-image-intro"><img src="<?php echo JURI::root().$image->image_intro; ?>"></div>
					<div class="article-body mod-articles-category-body">
					<div class="article-header mod-articles-header">
						<h2 class="introheading">
						<?php if ($params->get('link_titles') == 1) : ?>
							<a class="mod-articles-category-title <?php echo $item->active; ?>" href="<?php echo $item->link; ?>">
								<?php echo $item->title; ?>
							</a>
						<?php else : ?>
						<?php echo $item->title; ?>
						<?php endif; ?>
					</h2>
					</div>
					<div class="article-info">
						<?php if ($params->get('show_author')) : ?>
							<span class="createdby">
								<span><?php echo $item->displayAuthorName; ?></span>
							</span>
						<?php endif; ?>

						<?php if ($item->displayCategoryTitle) : ?>
							<span class="category-name">
								<?php echo $item->displayCategoryTitle; ?>
							</span>
						<?php endif; ?>

						<?php if ($item->displayDate) : ?>
							<span class="published">
								<time><?php echo $item->displayDate; ?></time>
							</span>
						<?php endif; ?>
					</div>
					<div class="article-introtext">
					<?php if ($params->get('show_introtext')) : ?>
						<p class="mod-articles-category-introtext">
							<?php echo $item->displayIntrotext; ?>
						</p>
					<?php endif; ?>
					</div>
					<?php if ($params->get('show_readmore')) : ?>
						<div class="readmore">
							<a class="mod-articles-category-title <?php echo $item->active; ?>" href="<?php echo $item->link; ?>">
								<?php if ($item->params->get('access-view') == false) : ?>
									<?php echo JText::_('MOD_ARTICLES_CATEGORY_REGISTER_TO_READ_MORE'); ?>
								<?php elseif ($readmore = $item->alternative_readmore) : ?>
									<?php echo $readmore; ?>
									<?php echo JHtml::_('string.truncate', $item->title, $params->get('readmore_limit')); ?>
										<?php if ($params->get('show_readmore_title', 0) != 0) : ?>
											<?php echo JHtml::_('string.truncate', $this->item->title, $params->get('readmore_limit')); ?>
										<?php endif; ?>
								<?php elseif ($params->get('show_readmore_title', 0) == 0) : ?>
									<?php echo JText::sprintf('MOD_ARTICLES_CATEGORY_READ_MORE_TITLE'); ?>
								<?php else : ?>
									<?php echo JText::_('MOD_ARTICLES_CATEGORY_READ_MORE'); ?>
									<?php echo JHtml::_('string.truncate', $item->title, $params->get('readmore_limit')); ?>
								<?php endif; ?>
							</a>
						</div>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>
		</div>
		<?php endforeach; ?>




	<?php else : ?>
		<?php foreach ($list as $item) : $image = json_decode($item->images); ?>
			<div class="article mod-articles-category-group">
				<div class="category-image-intro"><img src="<?php echo JURI::root().$image->image_intro; ?>"></div>
				<div class="article-body mod-articles-category-body">
				<div class="article-header mod-articles-header">
				<h2 class="introheading">
				<?php if ($params->get('link_titles') == 1) : ?>
					<a class="mod-articles-category-title <?php echo $item->active; ?>" href="<?php echo $item->link; ?>">
						<?php echo $item->title; ?>
					</a>
				<?php else : ?>
					<?php echo $item->title; ?>
				<?php endif; ?>
			</h2>
				</div>
				<div class="article-info">
				<?php if ($params->get('show_author')) : ?>
					<span class="createdby">
						<span><?php echo $item->displayAuthorName; ?></span>
					</span>
				<?php endif; ?>

				<?php if ($item->displayCategoryTitle) : ?>
					<span class="category-name">
						<?php echo $item->displayCategoryTitle; ?>
					</span>
				<?php endif; ?>

				<?php if ($item->displayDate) : ?>
					<span class="published">
						<time><?php echo $item->displayDate; ?></time>
					</span>
				<?php endif; ?>
				</div>
				<div class="article-introtext">
				<?php if ($params->get('show_introtext')) : ?>
					<p class="mod-articles-category-introtext">
						<?php echo $item->displayIntrotext; ?>
					</p>
				<?php endif; ?>
				</div>
				<?php if ($params->get('show_readmore')) : ?>
					<div class="readmore">
						<a class="mod-articles-category-title <?php echo $item->active; ?>" href="<?php echo $item->link; ?>">
							<?php if ($item->params->get('access-view') == false) : ?>
								<?php echo JText::_('MOD_ARTICLES_CATEGORY_REGISTER_TO_READ_MORE'); ?>
							<?php elseif ($readmore = $item->alternative_readmore) : ?>
								<?php echo $readmore; ?>
								<?php echo JHtml::_('string.truncate', $item->title, $params->get('readmore_limit')); ?>
							<?php elseif ($params->get('show_readmore_title', 0) == 0) : ?>
								<?php echo JText::sprintf('MOD_ARTICLES_CATEGORY_READ_MORE_TITLE'); ?>
							<?php else : ?>
								<?php echo JText::_('MOD_ARTICLES_CATEGORY_READ_MORE'); ?>
								<?php echo JHtml::_('string.truncate', $item->title, $params->get('readmore_limit')); ?>
							<?php endif; ?>
						</a>
					</div>
				<?php endif; ?>
			</div>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
