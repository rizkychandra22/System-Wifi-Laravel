import { PageProps as InertiaPageProps } from "./index";
import { route as ziggyRoute } from "ziggy-js";

declare global {
  var route: typeof ziggyRoute;
}

declare module "@inertiajs/core" {
  interface PageProps extends InertiaPageProps {}
}

declare module "@inertiajs/react" {
  interface PageProps extends InertiaPageProps {}
}
