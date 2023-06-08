import { Layout } from "@shopify/polaris";

import EnablePluginSection from "./EnablePluginSection";
import GeneralSettingsForm from ".//GeneralSettingsForm";

const GeneralForm = ({
  isDirty,
  settings,
  isSaving,
  isLoading,
  saveSettings,
  handleSubmit,
  fetchSettings,
  handleFieldChange,
}) => {
  return (
    <Layout.AnnotatedSection
      title="General Settings"
      description="Configure the settings for the Omniful Core Plugin."
    >
      <EnablePluginSection
        settings={settings}
        isSaving={isSaving}
        isLoading={isLoading}
        saveSettings={saveSettings}
        fetchSettings={fetchSettings}
      />
      <GeneralSettingsForm
        isDirty={isDirty}
        settings={settings}
        isSaving={isSaving}
        isLoading={isLoading}
        handleSubmit={handleSubmit}
        handleFieldChange={handleFieldChange}
      />
    </Layout.AnnotatedSection>
  );
};

export default GeneralForm;
