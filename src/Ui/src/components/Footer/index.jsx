import React from "react";

const Footer = () => {
  const currentYear = new Date().getFullYear();
  return (
    <div
      style={{
        textAlign: "center",
      }}
    >
      <div style={{ marginLeft: "16px" }}>
        <p style={{ margin: 0, padding: "8px 0", fontSize: "14px" }}>
          © {currentYear} Omniful Inc. All Rights Reserved.
        </p>
      </div>
    </div>
  );
};

export default Footer;
