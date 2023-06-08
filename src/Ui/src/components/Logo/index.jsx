import React from "react";
import { Image } from "@shopify/polaris";

const logoAlt = "Omniful logo";
const logoSrc =
  "https://www.omniful.com/_next/static/media/logo-primary-typographic-blue-white.f0ee5967.svg";

const Footer = () => {
  return (
    <div style={{ marginLeft: "1rem" }}>
      <Image width={100} alt={logoAlt} source={logoSrc} />
    </div>
  );
};

export default Footer;
