import {
  Text,
  Box,
  Card,
  Badge,
  Button,
  Inline,
  AlphaStack,
  useBreakpoints,
} from "@shopify/polaris";
import { useState, useCallback, useEffect } from "react";
import { CircleInformationMajor } from "@shopify/polaris-icons";

const EnableDebuggingSection = ({
  settings,
  isSaving,
  isLoading,
  saveSettings,
  fetchSettings,
}) => {
  const { mdDown } = useBreakpoints();
  const [enabled, setEnabled] = useState(false);

  useEffect(() => {
    if (settings && !isLoading && !isSaving) {
      setEnabled(settings? settings.enableDebugging : false);
    }
  }, [settings]);

  const handleToggle = useCallback(
    async (event) => {
      const enabledValue = !enabled;
      setEnabled(enabledValue);

      const jsonData = {
        enableDebugging: enabledValue,
      };

      await saveSettings(jsonData);
      await fetchSettings();
    },
    [saveSettings, enabled]
  );

  const title = "Debugging Mode";
  const toggleId = "setting-toggle-uuid";
  const badgeContent = enabled ? "On" : "Off";
  const badgeStatus = enabled ? "success" : undefined;
  const contentStatus = enabled ? "Turn off" : "Turn on";
  const descriptionId = "setting-toggle-description-uuid";
  const description =
    "Simulate transactions to test your checkout and order flows. When test mode is on, checkout does not accept real credit cards.";

  const settingStatusMarkup = (
    <Badge
      status={badgeStatus}
      statusAndProgressLabelOverride={`Setting is ${badgeContent}`}
    >
      {badgeContent}
    </Badge>
  );

  const helpLink = (
    <Button
      plain
      disabled={isLoading || isSaving}
      icon={CircleInformationMajor}
      accessibilityLabel="Learn more"
    />
  );

  const settingTitle = title ? (
    <Inline gap="2" wrap={false}>
      <Inline gap="2" align="start" blockAlign="baseline">
        <label htmlFor={toggleId}>
          <Text variant="headingMd" as="h6">
            {title}
          </Text>
        </label>
        <Inline gap="2" align="center" blockAlign="center">
          {settingStatusMarkup}
          {helpLink}
        </Inline>
      </Inline>
    </Inline>
  ) : null;

  const actionMarkup = (
    <Button
      role="switch"
      id={toggleId}
      disabled={isLoading || isSaving}
      ariaChecked={enabled ? "true" : "false"}
      onClick={handleToggle}
      size="slim"
    >
      {contentStatus}
    </Button>
  );

  const headerMarkup = (
    <Box width="100%">
      <Inline gap="12" align="space-between" blockAlign="start" wrap={false}>
        {settingTitle}
        {!mdDown ? (
          <Box minWidth="fit-content\">
            <Inline align="end">{actionMarkup}</Inline>
          </Box>
        ) : null}
      </Inline>
    </Box>
  );

  const descriptionMarkup = (
    <AlphaStack gap="4">
      <Text id={descriptionId} variant="bodyMd" as="p" color="subdued">
        {description}
      </Text>
      {mdDown ? (
        <Box width="100%">
          <Inline align="start">{actionMarkup}</Inline>
        </Box>
      ) : null}
    </AlphaStack>
  );

  return (
    <Card sectioned>
      <AlphaStack gap={{ xs: "4", sm: "5" }}>
        <Box width="100%">
          <AlphaStack gap={{ xs: "2", sm: "4" }}>
            {headerMarkup}
            {descriptionMarkup}
          </AlphaStack>
        </Box>
      </AlphaStack>
    </Card>
  );
};

export default EnableDebuggingSection;
