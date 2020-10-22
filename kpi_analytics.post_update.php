<?php

/**
 * @file
 * Post update functions for KPI Analytics module.
 */

/**
 * Update allowed vocabularies in the 'field_kpi_term' field.
 */
function kpi_analytics_post_update_update_allowed_vocabularies() {
  _kpi_analytics_update_allowed_vocabularies();
}
