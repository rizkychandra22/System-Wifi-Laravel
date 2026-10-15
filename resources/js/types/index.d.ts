export interface User {
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

export interface FlashMessages {
  success: string | null;
  error: string | null;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
  auth: {
    user: User | null;
  };
  flash: FlashMessages;
  app_name: string;
};
