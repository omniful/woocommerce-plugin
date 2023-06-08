import { Layout } from "@shopify/polaris";

import DeveloperSettingsForm from "./DeveloperSettingsForm";
import EnableDebuggingSection from "./EnableDebuggingSection";

const DeveloperForm = ({
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
      title="Developer Settings"
      description="Configure the developer settings for the Omniful Core Plugin."
    >
      <EnableDebuggingSection
        settings={settings}
        isSaving={isSaving}
        isLoading={isLoading}
        saveSettings={saveSettings}
        fetchSettings={fetchSettings}
      />
      {/* <DeveloperSettingsForm
        isDirty={isDirty}
        settings={settings}
        isSaving={isSaving}
        isLoading={isLoading}
        handleSubmit={handleSubmit}
        handleFieldChange={handleFieldChange}
      /> */}
    </Layout.AnnotatedSection>
  );
};

export default DeveloperForm;
