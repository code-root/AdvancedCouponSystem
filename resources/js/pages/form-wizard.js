/**
* Theme: Osen - Responsive Bootstrap 5 Admin Dashboard
* Author: Coderthemes
* Module/App: Form Wizard
*/
import { Wizard } from "./wizard";

new Wizard("#basicwizard"),
  new Wizard("#progressbarwizard", { progress: !0 }),
  new Wizard("#validation-wizard", { validate: !0 });
