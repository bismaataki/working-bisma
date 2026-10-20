<?php
/**
 * Theme-wide constants (identifier, menu locations, image sizes).
 *
 * @package Skaflow
 */

const SKAFLOW_IDENTIFIER = 'skaflow';
const SKAFLOW_PREFIX     = 'skaflow_';

/** @var string Primary navigation menu location (categories + right-pulled items). */
const SKAFLOW_MENU_PRIMARY = 'skaflow-primary-nav';

/** @var string Utility navigation menu location (Showrooms, Account, Quotes). */
const SKAFLOW_MENU_UTILITY = 'skaflow-utility-nav';

/** @var string Registered image size for desktop mega menu thumbnails (2× display). */
const SKAFLOW_MENU_THUMB_SIZE = 'skaflow-menu-thumb';

/** @var string Theme asset used when a menu thumb has no category/override image. */
const SKAFLOW_MENU_THUMB_PLACEHOLDER = 'assets/images/menu-thumb-placeholder.png';

/** @var int Display size (px) for menu thumbnails in CSS — 2× source uses SKAFLOW_MENU_THUMB_SIZE. */
const SKAFLOW_MENU_THUMB_DISPLAY = 54;
