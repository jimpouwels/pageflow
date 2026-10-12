## Template Variable Reference


### PageVisual (page scope)
| Key                   | Type   | Description                        |
|-----------------------|--------|------------------------------------|
| page.title            | string | The title of the page              |
| page.description      | string | The meta/SEO description           |
| page.seo_title        | string | The SEO title (was h1)             |
| page.url              | string | The URL of the page                |
| page.is_homepage      | bool   | True if this is the homepage       |
| page.navigation_title | string | The navigation title               |
| page.children         | array  | List of child pages (see below)    |

Each item in `page.children`:
| Key                   | Type   | Description                        |
|-----------------------|--------|------------------------------------|
| id                    | string | The ID of the child page           |
| title                 | string | The title of the child page        |
| url                   | string | The URL of the child page          |
| navigation_title      | string | The navigation title               |
| is_current_page       | bool   | True if this is the current page   |
| is_homepage           | bool   | True if this is the homepage       |

### TextElementFrontendVisual (text_element scope)
| Key           | Type   | Description                |
|---------------|--------|----------------------------|
| title         | string | The element's title        |
| text          | string | The element's text (HTML)  |
| text_wysiwyg  | string | Raw WYSIWYG content        |

### ListElementFrontendVisual (list_element scope)
| Key    | Type   | Description                |
|--------|--------|----------------------------|
| title  | string | The list's title           |
| items  | array  | List of items (see below)  |

Each item in `items`:
| Type   | Description                |
|--------|----------------------------|
| string | The text of the list item  |

### IFrameElementFrontendVisual (iframe_element scope)
| Key    | Type   | Description                |
|--------|--------|----------------------------|
| title  | string | The iframe's title         |
| url    | string | The iframe URL             |
| height | int    | The iframe height          |
| width  | int    | The iframe width           |

### SeparatorElementFrontendVisual (separator_element scope)
| Key    | Type   | Description                |
|--------|--------|----------------------------|
| title  | string | The separator's title      |

### HtmlElementFrontendVisual (html_element scope)
| Key    | Type   | Description                      |
|--------|--------|----------------------------------|
| html   | string | The raw HTML markup, as entered  |

### TableOfContentsElementFrontendVisual (table_of_contents_element scope)
| Key    | Type   | Description                |
|--------|--------|----------------------------|
| title  | string | The TOC's title            |
| items  | array  | List of TOC items (see below) |

Each item in `items`:
| Key      | Type   | Description                |
|----------|--------|----------------------------|
| title    | string | The item's title           |
| reference| string | The anchor reference       |

### PhotoAlbumElementFrontendVisual (photo_album_element scope)
| Key    | Type   | Description                |
|--------|--------|----------------------------|
| title  | string | The album's title          |
| images | array  | List of images (see below) |

Each item in `images`:
| Key         | Type   | Description                  |
|-------------|--------|------------------------------|
| id          | int    | Image ID                     |
| title       | string | Image title                  |
| alt_text    | string | Image alt text               |
| location    | string | Image file location          |
| url         | string | Full image URL               |
| url_mobile  | string | Mobile image URL             |
| width       | int    | Image width                  |
| height      | int    | Image height                 |

### ImageElementFrontendVisual (image_element scope)
| Key           | Type   | Description                  |
|---------------|--------|------------------------------|
| title         | string | The element's title          |
| img_title     | string | Image title                  |
| img_alt_text  | string | Image alt text               |
| img_location  | string | Image file location          |
| align         | string | Alignment (left/center/right)|
| width         | int    | Image width                  |
| height        | int    | Image height                 |
| image_url     | string | Full image URL               |
| image_url_mobile | string | Mobile image URL           |
| extension     | string | File extension               |
| link.open_tag | string | Opening <a> tag if linked    |
| link.close_tag| string | Closing </a> tag if linked   |

### ArticleVisual (article scope)
| Key                | Type   | Description                        |
|--------------------|--------|------------------------------------|
| article.title      | string | The title of the article           |
| article.description| string | The meta/SEO description           |
| article.seo_title  | string | The SEO title.                     |
| article.url        | string | The URL of the article             |
| article.parent_article | object | The parent article (see below)    |

If present, `article.parent_article`:
| Key           | Type   | Description                        |
|---------------|--------|------------------------------------|
| id            | string | The ID of the parent article       |
| title         | string | The title of the parent article    |
| seo_title     | string | The SEO title of the parent        |
| description   | string | The meta/SEO description           |
| url           | string | The URL of the parent article      |
| ...           | mixed  | Metadata fields (see code)         |

### FormFrontendVisual (form scope)
| Key           | Type   | Description                  |
|---------------|--------|------------------------------|
| webform_id    | int    | The webform's ID             |
| captcha_key   | string | Captcha key (if enabled)     |
| title         | string | Webform title                |
| form_html     | string | Rendered form HTML           |
| is_submitted  | bool   | True if form was submitted   |
| has_captcha_error | bool | Captcha error present      |

| Key           | Type   | Description                  |
|---------------|--------|------------------------------|
| label         | string | Field label                  |
| name          | string | Field name                   |
| value         | mixed  | Field value                  |
| has_error     | bool   | Field has error              |
| mandatory     | bool   | Field is mandatory           |
| form_item_html| string | Rendered item HTML           |
| form_field_html| string| Rendered field HTML          |


## Creating Template Variants

You can create variants of your templates by adding a variable `$var.some_variable`. All `{assign}` statements that use a var with prefix `$var.` will be processed as parameterizable template variables that can be configured in the CMS.

```smarty
{assign var=someClass value=$var.some_class}
	<div class="{$someClass}">Special variant!</div>
{/if}
```

