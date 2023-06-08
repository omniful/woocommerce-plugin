import React, { useState, useEffect } from "react";
import {
  Card,
  Page,
  Frame,
  Toast,
  Layout,
  ContextualSaveBar,
} from "@shopify/polaris";
import axios from "redaxios";
import Logo from "./components/Logo";
import Footer from "./components/Footer";
import GeneralForm from "./components/GeneralForm";
import DeveloperForm from "./components/DeveloperForm";
import InformativeBanner from "./components/InformativeBanner";

const DEV_URL = "http://omniful.local/";
const PROD_URL = "https://omniful-wp.flywheelsites.com/";

const App = () => {
  const [settings, setSettings] = useState({
    webhookUrl: "",
    accessToken: "",
    workspaceId: "",
    isEnabled: false,
    webhookToken: "",
    enableDebugging: false,
  });
  const [isDirty, setIsDirty] = useState(false);
  const [isSaving, setIsSaving] = useState(false);
  const [isLoading, setIsLoading] = useState(true);
  const [showToast, setShowToast] = useState(false);
  const [toastMessage, setToastMessage] = useState("");

  useEffect(() => {
    fetchSettings();
  }, []);

  const fetchSettings = async () => {
    setIsLoading(true);
    try {
      const { data } = await axios.get(
        process.env.NODE_ENV === "development"
          ? `${DEV_URL}/wp-json/V2/options`
          : `${PROD_URL}/wp-json/V2/options`
      );

      setSettings(data.data);
      setIsLoading(false);
    } catch (error) {
      console.error(error);
      setIsLoading(false);
      setShowToast(true);
      setToastMessage("Failed to call API, maybe you are disconnected!");
    }
  };

  const saveSettings = async (data = null) => {
    const payload = data ? data : settings;

    setIsSaving(true);
    try {
      const { data } = await axios.post(
        process.env.NODE_ENV === "development"
          ? `${DEV_URL}/wp-json/V2/options`
          : `${PROD_URL}/wp-json/V2/options`,
        payload
      );
      setShowToast(true);
      setToastMessage(`${data.message}`);
      setIsDirty(false);
      setIsSaving(false);
    } catch (error) {
      console.error(error);
      setIsSaving(false);
      setShowToast(true);
      setToastMessage("Failed to save settings");
    }
  };

  const handleFieldChange = async (field, value) => {
    setSettings((prevState) => ({ ...prevState, [field]: value }));

    // if (field.indexOf("enable") !== -1 && field.indexOf("active") !== -1) {
    setIsDirty(true);
    // }
  };

  const handleDiscard = () => {
    setIsDirty(false);
    setSettings({
      webhookUrl: "",
      workspaceId: "",
      accessToken: "",
      isEnabled: false,
      webhookToken: "",
      enableDebugging: false,
    });
    fetchSettings();
  };

  const handleSubmit = async (event) => {
    event.preventDefault();
    await saveSettings();
  };

  const description = (
    <div style={{ marginLeft: "16px" }}>
      <p style={{ margin: 0, padding: "8px 0", fontSize: "14px" }}>
        Configure the settings for the Omniful Core Plugin.
      </p>
    </div>
  );

  const toastMarkup = () => {
    if (showToast) {
      return (
        <Toast
          content={toastMessage}
          duration={3000}
          onDismiss={() => setShowToast(false)}
        />
      );
    }
    return null;
  };

  return (
    <Frame>
      <Card.Section subdued>
        <div style={{ display: "flex", alignItems: "center" }}>
          <Logo />
          {description}
        </div>
      </Card.Section>
      <Page>
        <Layout>
          <GeneralForm
            isDirty={isDirty}
            settings={settings}
            isSaving={isSaving}
            isLoading={isLoading}
            saveSettings={saveSettings}
            handleSubmit={handleSubmit}
            fetchSettings={fetchSettings}
            handleFieldChange={handleFieldChange}
          />
          <DeveloperForm
            isDirty={isDirty}
            settings={settings}
            isSaving={isSaving}
            isLoading={isLoading}
            saveSettings={saveSettings}
            handleSubmit={handleSubmit}
            fetchSettings={fetchSettings}
            handleFieldChange={handleFieldChange}
          />
        </Layout>
        {/* UTILS */}
        {toastMarkup()}
        {isDirty && (
          <ContextualSaveBar
            message="Unsaved changes"
            saveAction={{ onAction: handleSubmit, loading: isSaving }}
            discardAction={{ onAction: handleDiscard }}
          />
        )}
        <InformativeBanner />
        <Footer />
      </Page>
    </Frame>
  );
};

export default App;
