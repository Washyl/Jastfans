/**
 * Privacy settings component
 */
"use strict";
/* global GeneralSettings, app, launchToast, trans, reload, NotificationsSettings, userGeoBlocking */

$(function () {
  $(".custom-control-input").on("change", function () {
    const key = $(this).attr("id");
    const val = $(this).prop("checked");
    GeneralSettings.updateFlagSetting(key, val);
  });
});
