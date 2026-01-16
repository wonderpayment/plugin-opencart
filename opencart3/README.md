WonderPayment for OpenCart 3 (Standalone Package)

What this package includes
- WonderPayment extension files under `upload/`
- WonderPayment SDK under `upload/system/storage/vendor/wonderpayment/`
- `install.xml` for OpenCart Extension Installer metadata

Install (manual)
1) Copy the `upload/` folder into your OpenCart root.
2) In Admin: Extensions -> Extensions -> Payments, find WonderPayment and click Install.
3) Configure WonderPayment settings and save.

Notes
- This package does not include any exchange-rate conversion logic; it uses OpenCart's own currency system.
- Install step will ensure HKD currency exists (it will not change the default currency).
