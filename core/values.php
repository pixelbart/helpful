<?php
/**
 * Helpful Frontend Helpers for retrieving 
 * values from database
 * @since 4.0.0
 */

/**
 * @see classes/class-helpful-helper-values.php
 */
if( !function_exists("helpful_get_pro") ) {
  function helpful_get_pro($post_id = null) {
    return Helpful_Helper_Stats::getPro($post_id);
  }
}

/**
 * @see classes/class-helpful-helper-values.php
 */
if( !function_exists("helpful_get_contra") ) {
  function helpful_get_contra($post_id = null) {
    return Helpful_Helper_Stats::getContra($post_id);
  }
}

/**
 * @see classes/class-helpful-helper-values.php
 */
if( !function_exists("helpful_get_pro_all") ) {
  function helpful_get_pro_all() {
    return Helpful_Helper_Stats::getProAll();
  }
}

/**
 * @see classes/class-helpful-helper-values.php
 */
if( !function_exists("helpful_get_contra_all") ) {
  function helpful_get_contra_all() {
    return Helpful_Helper_Stats::getContraAll();
  }
}