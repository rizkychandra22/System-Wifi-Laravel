/// <reference types="vite/client" />

import { route as ziggyRoute } from "ziggy-js";

declare global {
  var route: typeof ziggyRoute;

  interface User {
    id: number;
    name: string;
    phone: string;
    role: "admin" | "employee" | "customer";
    address: string | null;
    device_id: string | null;
    registered_by_id: number | null;
    created_at?: string;
    updated_at?: string;
  }

  interface FlashMessages {
    success: string | null;
    error: string | null;
  }

  type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
      user: User | null;
    };
    flash: FlashMessages;
    app_name: string;
  };
}

declare module "@inertiajs/core" {
  interface PageProps {
    auth: {
      user: User | null;
    };
    flash: FlashMessages;
    app_name: string;
  }
}

declare module "@inertiajs/react" {
  interface PageProps {
    auth: {
      user: User | null;
    };
    flash: FlashMessages;
    app_name: string;
  }
}

export type { User, FlashMessages, PageProps };
