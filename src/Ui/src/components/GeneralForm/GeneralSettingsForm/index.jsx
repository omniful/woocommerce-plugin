import React, { useState } from "react";
import {
  Card,
  Button,
  Spinner,
  TextField,
  FormLayout,
  ButtonGroup,
  Form as PolarisForm,
  Icon,
} from "@shopify/polaris";

import { ViewMajor, HideMinor } from "@shopify/polaris-icons";

const GeneralSettingsForm = ({
  settings,
  isDirty,
  isLoading,
  isSaving,
  handleSubmit,
  handleFieldChange,
}) => {
  // initialize two states to track the visibility of Webhook Token and Access Token
  const [showWebhookToken, setShowWebhookToken] = useState(false);
  const [showAccessToken, setShowAccessToken] = useState(false);

  const allowShowSecrets = settings ? settings.enableDebugging : false;

  // function to toggle visibility of Webhook Token
  const toggleWebhookTokenVisibility = () => {
    setShowWebhookToken((showWebhookToken) => !showWebhookToken); // toggles the state between true and false
  };

  // function to toggle visibility of Access Token
  const toggleAccessTokenVisibility = () => {
    setShowAccessToken((showAccessToken) => !showAccessToken); // toggles the state between true and false
  };

  // markup for the Submit button, which will be disabled under certain conditions
  const submitButtonMarkup = (
    <Button primary submit disabled={!isDirty || isLoading || isSaving}>
      {isLoading || isSaving ? (
        <Spinner size="small" color="white" accessibilityLabel="Loading" />
      ) : (
        "Save"
      )}
    </Button>
  );

  // markup for the Webhook Token input field
  const webhookTokenInputMarkup = (
    <TextField
      label="Webhook Token"
      onChange={(value) => handleFieldChange("webhookToken", value)}
      placeholder="Enter your Webhook Token"
      type={
        allowShowSecrets ? (showWebhookToken ? "text" : "password") : "text"
      }
      value={settings ? settings.webhookToken : ""}
      disabled={isLoading || isSaving}
      required
      connectedRight={
        allowShowSecrets ? (
          <ButtonGroup>
            <Button
              plain
              icon={showWebhookToken ? HideMinor : ViewMajor}
              onClick={toggleWebhookTokenVisibility}
              aria-label={
                showWebhookToken ? "Hide webhook token" : "Show webhook token"
              }
            />
          </ButtonGroup>
        ) : null
      }
    />
  );

  // markup for the Access Token input field
  const accessTokenInputMarkup = (
    <TextField
      label="Access Token"
      onChange={(value) => handleFieldChange("accessToken", value)}
      placeholder="Enter your Access Token"
      type={allowShowSecrets ? (showAccessToken ? "text" : "password") : "text"}
      value={settings ? settings.accessToken : ""}
      disabled={isLoading || isSaving}
      required
      connectedRight={
        allowShowSecrets ? (
          <ButtonGroup>
            <Button
              plain
              icon={showAccessToken ? HideMinor : ViewMajor}
              onClick={toggleAccessTokenVisibility}
              aria-label={
                showAccessToken ? "Hide access token" : "Show access token"
              }
            />
          </ButtonGroup>
        ) : null
      }
    />
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
          {webhookTokenInputMarkup}
          <TextField
            label="Workspace ID"
            onChange={(value) => handleFieldChange("workspaceId", value)}
            placeholder="Enter your Workspace ID"
            disabled={isLoading || isSaving}
            value={settings ? settings.workspaceId : ""}
            required
          />
          {accessTokenInputMarkup}
          {submitButtonMarkup}
        </FormLayout>
      </PolarisForm>
    </Card>
  );
};

export default GeneralSettingsForm;
