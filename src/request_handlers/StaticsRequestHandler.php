<?php

namespace Pageflow\Core\request_handlers;

use const Pageflow\core\STATIC_DIR;

class StaticsRequestHandler extends HttpRequestHandler {

    public function __construct() {}

    public function handleGet(): void {
        $relativePath = $this->getRelativePathFromGetRequest();
        if (!empty($relativePath)) {
            // SECURITY: Block directory traversal and other path attacks
            if ($this->containsBlacklistedChars($relativePath) ||
                $this->containsBlacklistedChars($_GET["module"] ?? '') ||
                $this->containsBlacklistedChars($_GET["element"] ?? '')) {
                http_response_code(403);
                exit('Access denied');
            }
            
            $absolutePath = $this->getAbsolutePathFor($relativePath);
            $this->setResponseContentType($absolutePath);
            readfile(explode('?', $absolutePath)[0]);
        }
    }

    public function handlePost(): void {}

    public function isPublicFileRequest(): bool {
        $relPath = $this->getRelativePathFromGetRequest();
        // SECURITY MEASURE: Do NOT allow '..' to be present in the path, since this would
        // allow any file in the webserver to be downloaded.
        return str_starts_with($relPath, "/public") && !str_contains($relPath, "..");
    }

    public static function isFileRequest(): bool {
        return isset($_GET["file"]);
    }

    private function setResponseContentType(string $absolutePath): void {
        $pathParts = explode(".", $absolutePath);
        $extension = $pathParts[count($pathParts) - 1];
        if ($extension == "jpg") {
            header("Content-Type: image/jpeg");
        } else if ($extension == "gif") {
            header("Content-Type: image/gif");
        } else if ($extension == "png") {
            header("Content-Type: img/png");
        } else if ($extension == "svg") {
            header("Content-Type: image/svg+xml");
        } else if ($extension == "css") {
            header("Content-Type: text/css");
        } else if ($extension == "js") {
            header("Content-Type: text/javascript");
        } else if ($extension == "ttf") {
            header("Content-Type: application/x-font-ttf");
        }
    }

    private function getAbsolutePathFor(string $relativePath): string {
        // New cleaner URL structure: ?module=images&file=css/images.css
        if (isset($_GET["module"]) && isset($_GET["file"])) {
            return dirname(__DIR__) . '/modules/' . $_GET["module"] . '/static/' . $_GET["file"];
        }
        
        // New cleaner URL structure: ?element=text_element&file=css/text.css
        if (isset($_GET["element"]) && isset($_GET["file"])) {
            return dirname(__DIR__) . '/elements/' . $_GET["element"] . '/static/' . $_GET["file"];
        }
        
        // Backwards compatibility: old URL structure with /modules/ or /elements/ in path
        if (str_starts_with($relativePath, '/modules/') || str_starts_with($relativePath, '/elements/')) {
            $parts = explode('/', ltrim($relativePath, '/'), 3);
            if (count($parts) === 3) {
                $colocatedPath = dirname(__DIR__) . '/' . $parts[0] . '/' . $parts[1] . '/static/' . $parts[2];
                if (file_exists($colocatedPath)) {
                    return $colocatedPath;
                }
            }
        }
        
        return STATIC_DIR . $relativePath;
    }

    private function getRelativePathFromGetRequest(): ?string {
        if (isset($_GET["file"]) && $_GET["file"] != "") {
            return $_GET["file"];
        }
        return null;
    }

    private function containsBlacklistedChars(string $param): bool {
        $blacklist = [
            '..',  // Parent directory traversal
            '~',   // Home directory
            "\0",  // Null byte
            '%00', // URL encoded null byte
        ];
        
        foreach ($blacklist as $forbidden) {
            if (str_contains($param, $forbidden)) {
                return true;
            }
        }
        
        return false;
    }
}