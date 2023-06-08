import ReactDOM from "react-dom";
import { AppProvider } from "@shopify/polaris";
// import { IntercomProvider } from "react-use-intercom";
import enTranslations from "@shopify/polaris/locales/en.json";

import App from "./app";

import "./styles/styles.css";
import "@shopify/polaris/build/esm/styles.css";

const INTERCOM_APP_ID = "an6qx7n5";

ReactDOM.createRoot(
  document.querySelector("#wp-admin-plugin-page-root")
).render(
  <AppProvider i18n={enTranslations}>
    {/* <IntercomProvider
      appId={INTERCOM_APP_ID}
      autoBoot
      autoBootProps={{ language_override: "en", alignment: "right" }}
    > */}
    <App />
    {/* </IntercomProvider> */}
  </AppProvider>
);
