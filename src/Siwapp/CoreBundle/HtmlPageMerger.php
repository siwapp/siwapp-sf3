<?php

namespace Siwapp\CoreBundle;

class HtmlPageMerger
{
    public function merge(array $pages, $separator = '')
    {
        $head = '';
        $output = '';
        libxml_use_internal_errors(true);
        foreach ($pages as $page) {
            if (!$head) {
                list($head, $body) = explode('<body', $page);
            }
            $document = new \DOMDocument;
            $document->loadHTML($page);
            $bodyOnlyDocument = new \DOMDocument;
            $body = $document->getElementsByTagName('body')->item(0);
            foreach ($body->childNodes as $child){
                $bodyOnlyDocument->appendChild($bodyOnlyDocument->importNode($child, true));
            }
            $output .= $bodyOnlyDocument->saveHtml() . $separator;
        }

        return $head . '<body>' . rtrim($output, $separator) . '</body></html>';
    }
}
