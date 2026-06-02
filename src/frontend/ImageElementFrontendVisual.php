<?php

namespace Pageflow\Core\frontend;

use Pageflow\Core\elements\image_element\ImageElement;
use Pageflow\Core\modules\articles\model\Article;
use Pageflow\Core\modules\blocks\model\Block;
use Pageflow\Core\modules\pages\model\Page;

class ImageElementFrontendVisual extends ElementFrontendVisual {

    public function __construct(Page $page, ?Article $article, ?Block $block, ImageElement $imageElement) {
        parent::__construct($page, $article, $block, $imageElement);
    }

    public function loadElement(array &$data): void {
        $data["title"] = $this->getElement()->getTitle();
        $data["img_title"] = $this->getElement()->getImage()?->getTitle();
        $data["img_alt_text"] = $this->getElement()->getImage()?->getAltText();
        $data["location"] = $this->getElement()->getImage()?->getLocation();
        $data["align"] = $this->getElement()->getAlign();
        $data["width"] = $this->getElement()->getWidth();
        $data["height"] = $this->getElement()->getHeight();
        $data["image_url"] = $this->createImageUrl();
        $data["image_url_mobile"] = $this->createMobileImageUrl();
        $data["extension"] = $this->getExtension();

        $openTag = "";
        $closeTag = "";
        if ($this->getElement()->getLink()) {
            $openTag = "<a href=\"{$this->getElement()->getLink()}\" target=\"_blank\" title=\"{$this->getElement()->getImage()?->getTitle()}\">";
            $closeTag = "</a>";
        }
        $linkData['open_tag'] = $openTag;
        $linkData['close_tag'] = $closeTag;
        $data['link'] = $linkData;
    }

    private function createImageUrl(): string {
        return $this->getElement()->getUrl() ?: $this->getLinkHelper()->createImageUrl($this->getElement()->getImage());
    }

    private function createMobileImageUrl(): string {
        return $this->getElement()->getUrl() ?: $this->getLinkHelper()->createMobileImageUrl($this->getElement()->getImage());
    }

    private function getExtension(): string {
        $image = $this->getElement()->getImage();
        return $image ? $image->getExtension() : "";
    }
}