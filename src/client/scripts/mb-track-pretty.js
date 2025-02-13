//
// This function creates a basic queue system for the tracking script.
// It allows for calls to it to be made before the tracking script is loaded.
//
(
  function (windowReference, mbTrackString) {
    if (!windowReference[mbTrackString]) {
      windowReference[mbTrackString] = function () {
        ;
        (
          windowReference[mbTrackString].q = windowReference[mbTrackString].q || []
        ).push(arguments)
      }
      windowReference[mbTrackString].q = windowReference[mbTrackString].q || []
    }
  }
)(
  window,
  'mb_track'
);