import React from "react";
import { Link, Banner } from "@shopify/polaris";
// import { useIntercom } from "react-use-intercom";

const InformativeBanner = () => {
  // const { show } = useIntercom();

  return (
    <Banner
      title="Integration warning"
      status="warning"
      style={{ marginTop: "20px", marginBottom: "20px" }}
    >
      <p>
        This is a warning banner to let you know that the Omniful Core Plugin might
        not work as expected if your settings are incorrect. If you have any
        issues, please{" "}
        {/* <Link onClick={show} external monochrome>
          contact us
        </Link> */}
        .
      </p>
    </Banner>
  );
};

export default InformativeBanner;
