<?php
/**
 * @file
 * Mock global functions for this namespace
 */
namespace Dennis\Link\Checker;

/**
 * Copy of the drupal function in filter.module
 */
function filter_dom_load($text) {
  $dom_document = new \DOMDocument();
  // Ignore warnings during HTML soup loading.
  @$dom_document->loadHTML('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>' . $text . '</body></html>');

  return $dom_document;
}

/**
 * Copy of the drupal function in filter.module
 */
function filter_dom_serialize($dom_document) {
  $body_node = $dom_document->getElementsByTagName('body')->item(0);
  $body_content = '';

  if ($body_node !== NULL) {
    foreach ($body_node->getElementsByTagName('script') as $node) {
      filter_dom_serialize_escape_cdata_element($dom_document, $node);
    }

    foreach ($body_node->getElementsByTagName('style') as $node) {
      filter_dom_serialize_escape_cdata_element($dom_document, $node, '/*', '*/');
    }

    foreach ($body_node->childNodes as $child_node) {
      $body_content .= $dom_document->saveXML($child_node);
    }
    return preg_replace('|<([^> ]*)/>|i', '<$1 />', $body_content);
  }
  else {
    return $body_content;
  }
}
