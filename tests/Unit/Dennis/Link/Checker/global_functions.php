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
