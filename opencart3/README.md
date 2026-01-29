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
- SDK is not auto-updated. If you want to update the SDK after installation, run `composer update` in `upload/`.

OCMOD (Return Refund UI)
- This package ships an OCMOD file at `upload/system/ocmod/wonderpayment_return_refund_ui.ocmod.xml`.
- It injects WonderPayment refund UI + logic into:
  - `admin/controller/sale/return.php`
  - `admin/view/template/sale/return_list.twig`
- The OCMOD uses full-file replacement to ensure the refund UI and workflow are available in the returns list.

How to enable
1) Upload the `upload/` folder into OpenCart root.
2) In Admin: Extensions -> Extensions -> Payments, install WonderPayment.
3) In Admin: Extensions -> Modifications, click Refresh.

Development notes
- Because the OCMOD replaces full files, any custom changes in those core files will be overwritten at runtime by the modification.
- If you need to customize the refund UI/logic, update the source files under `upload/admin/...` and regenerate the OCMOD file.
