import React from "react";
import {
  Card,
  Button,
  Spinner,
  TextField,
  FormLayout,
  Form as PolarisForm,
} from "@shopify/polaris";

const DeveloperSettingsForm = ({
  settings,
  isDirty,
  isLoading,
  isSaving,
  handleSubmit,
  handleFieldChange,
}) => {
  const submitButtonMarkup = (
    <Button primary submit disabled={!isDirty || isLoading || isSaving}>
      {isLoading || isSaving ? (
        <Spinner size="small" color="white" accessibilityLabel="Loading" />
      ) : (
        "Save"
      )}
    </Button>
  );

  return (
    <Card sectioned>
      <PolarisForm onSubmit={handleSubmit}>
        <FormLayout>
          <TextField
            label="Webhook Url"
            onChange={(value) => handleFieldChange("webhookUrl", value)}
            placeholder="Enter your Webhook Url"
            disabled={isLoading || isSaving}
            value={settings ? settings.webhookUrl : ""}
            required
          />

          <div className="input-icon-container">
            <TextField
              label="Webhook Token"
              onChange={(value) => handleFieldChange("webhookToken", value)}
              placeholder="Enter your Webhook Token"
              type="password"
              value={settings ? settings.webhookToken : ""}
              disabled={isLoading || isSaving}
              required
            />
          </div>
          <div className="input-icon-container">
            <TextField
              label="Workspace Id"
              onChange={(value) => handleFieldChange("workspaceId", value)}
              placeholder="Enter your workspace id"
              type="password"
              value={settings ? settings.workspaceId : ""}
              disabled={isLoading || isSaving}
              required
            />
          </div>
          <div className="input-icon-container">
            <TextField
              label="Access Token"
              onChange={(value) => handleFieldChange("accessToken", value)}
              placeholder="Enter your Access Token"
              type="password"
              value={settings ? settings.accessToken : ""}
              disabled={isLoading || isSaving}
              required
            />
          </div>
          {submitButtonMarkup}
        </FormLayout>
      </PolarisForm>
    </Card>
  );
};

export default DeveloperSettingsForm;
