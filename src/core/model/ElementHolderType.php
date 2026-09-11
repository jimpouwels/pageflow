<?php

namespace Pageflow\Core\core\model;

enum ElementHolderType: string {
    case PAGE = 'ELEMENT_HOLDER_PAGE';
    case ARTICLE = 'ELEMENT_HOLDER_ARTICLE';
    case BLOCK = 'ELEMENT_HOLDER_BLOCK';
}