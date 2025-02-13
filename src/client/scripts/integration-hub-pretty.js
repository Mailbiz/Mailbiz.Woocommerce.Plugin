//
// This function inserts a script tag into the DOM that loads the integration script.
// This integration script receives the integrationKey that is set in the admin panel and loads the user features and preferences.
//
(
  function (
    windowReference,
    documentReference,
    scriptString,
    integrationScriptUrl,
    integrationKey,
    integrationAndLocalScriptElement,
    localSomeScriptElement
  ) {
    windowReference[integrationAndLocalScriptElement] ||
      (
        windowReference[integrationAndLocalScriptElement] = {
          id: integrationKey,
          ready: 0
        },
        integrationAndLocalScriptElement = documentReference.createElement(scriptString),
        localSomeScriptElement = documentReference.getElementsByTagName(scriptString)[0],
        integrationAndLocalScriptElement.async = 1,
        integrationAndLocalScriptElement.src = integrationScriptUrl,
        localSomeScriptElement.parentNode.insertBefore(integrationAndLocalScriptElement, localSomeScriptElement)
      )
  }
)(
  window,
  document,
  "script",
  "https://d3eq1zq78ux3cv.cloudfront.net/static/scripts/integration.min.js",
  document.getElementById('mailbiz-integration-hub-script').dataset.integrationKey,
  "MailbizIntegration"
);