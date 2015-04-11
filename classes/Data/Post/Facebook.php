<?php

/*
 * Copyright (C) 2015 Nathan Crause - All rights reserved
 *
 * This file is part of Socialfeed
 *
 * Copying, modification, duplication in whole or in part without
 * the express written consent of the copyright holder is
 * expressly prohibited under the Berne Convention and the
 * Buenos Aires Convention.
 */

namespace Claromentis\Socialfeed\Data\Post;

use Claromentis\Socialfeed\Data\Post;
use Claromentis\Socialfeed\Template\Engine;
use Claromentis\Socialfeed\Template\Factory;

/**
 * Description of Facebook
 *
 * @author Nathan Crause
 */
class Facebook extends Post {

	protected function extractRaw($src) {
		return (object)[
			'id'			=> $src->getProperty('id'),
			'object_id'		=> $src->getProperty('object_id'),
			'story'			=> $src->getProperty('story'),
			'story_tags'	=> $this->graphObjectAsArray($src->getProperty('story_tags')),
			'message'		=> $src->getProperty('message'),
			'message_tags'	=> $this->graphObjectAsArray($src->getProperty('message_tags')),
			'type'			=> $src->getProperty('type'),
			'link'			=> $src->getProperty('link'),
			'picture'		=> $src->getProperty('picture'),
			'name'			=> $src->getProperty('name'),
			'description'	=> $src->getProperty('description')
		];
		
//		die('Extracted <pre>' . print_r($this->raw, true) . '</pre>');
	}
	
	private function graphObjectAsArray($obj) {
		if ($obj) {
			return $obj->asArray();
		}
		
		return null;
	}
	
	private function markup($content, $tags) {
		$offset = 0;
		$html = '';
		
		foreach ($tags as $tag) {
//			die('<pre>' . print_r($tag, true) . '</pre>');
			$tag = reset($tag);
			$html .= substr($content, $offset, $tag->offset - $offset);
			$html .= "<a href=\"https://www.facebook.com/{$tag->id}\" target=\"_blank\">" 
					. substr($content, $tag->offset, $tag->length) . "</a>";
			
			$offset = $tag->offset + $tag->length;
		}
		$html .= substr($content, $offset);
		
		return str_replace("\n", "<br>", $html);
	}

	public function getHTML($template = 'socialfeed/facebook.html', Engine $engine = NULL, array $options = []) {
		$engine = $engine ?: Factory::raw();
		$args = [
			'title.body_html'	=> $this->markup($this->getRaw()->story,
					$this->getRaw()->story_tags, $options),
			'body.body_html'	=> $this->smartMarkup(
					$this->markup($this->getRaw()->message,
							$this->getRaw()->message_tags, $options), $options),
			// default to displaying no media
			'media.visible'		=> false
		];
		
		$this->addTypeSpecificArgs($args, $options);
		
		// last ditch test - is there even a title to display?
		$args['title.visible'] = !empty($args['title.body_html']);
		
		return $engine->render($template, $args);
	}
	
	public function getWrapperClassCSS() {
		return "socialfeed-facebook-{$this->getRaw()->type}";
	}

	/**
	 * Facebook offers a range of post types, each with slightly different
	 * way of generating content. This method builds those specifics.
	 */
	private function addTypeSpecificArgs(array &$args, array $options) {
		switch ($this->getRaw()->type) {
			case 'photo':
			case 'video':
				$args['media.visible'] = true;
				$args['link.href'] = $this->getRaw()->link;
				$args['picture.src'] = $this->getRaw()->picture;
				$args['caption_title.body'] = $this->getRaw()->name;
				$args['caption_title.visible'] = !empty($args['caption_title.body']);
				$args['caption_description.body'] = $this->getRaw()->description;
				$args['caption_description.visible'] = !empty($this->getRaw()->description);
				$args['origin.href'] = 'https://www.facebook.com/' . $this->getRaw()->object_id;
				$args['origin.target'] = $options[\Claromentis\Socialfeed\Component::OPT_LINK_TARGET] ?: '_blank';
				
				break;
				
			default:
				// all other types return no additional content
		}
	}
	
	private function smartMarkup($src, array $options) {
		$return = $this->findLinks($src, $options);
		$return = $this->findHashtags($return, $options);
		
		return $return;
	}
	
	private function findLinks($src, array $options) {
		$target = (@$options[\Claromentis\Socialfeed\Component::OPT_LINK_TARGET]
				?: '_blank');
			
		return preg_replace_callback('![a-z]+://[\w\.\d]+/?\S+!', function($matches) use ($target) {
			return "<a href=\"{$matches[0]}\" target=\"{$target}\">{$matches[0]}</a>";
		}, $src);
	}
	
	private function findHashtags($src, array $options) {
		$target = (@$options[\Claromentis\Socialfeed\Component::OPT_LINK_TARGET]
				?: '_blank');
			
		return preg_replace_callback('/[^\s]?#(\w+)/', function($matches) use ($target) {
			return '<a href="https://www.facebook.com/hashtag/'
					. $matches[1] . '?' . http_build_query([
						'story_id'	=> $this->getRaw()->id
					]) . "\" target=\"{$target}\">" . $matches[0] . '</a>';
		}, $src);
	}

}
