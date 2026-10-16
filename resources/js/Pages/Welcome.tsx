import React from "react";
import { Head } from "@inertiajs/react";
import { PageProps } from "@/Global.d";

interface WelcomeProps extends PageProps {
  title?: string;
  laravelVersion?: string;
  phpVersion?: string;
}

export default function Welcome({ title = "NetVerse System WiFi", auth, flash }: WelcomeProps) {
  return (
    <div className="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-center items-center p-6 selection:bg-blue-600 selection:text-white">
      <Head title={title} />

      <div className="max-w-2xl w-full bg-slate-900/80 border border-slate-800 rounded-2xl p-8 shadow-2xl backdrop-blur-xl">
        <div className="flex items-center space-x-3 mb-6">
          <div className="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center font-bold text-white shadow-lg shadow-blue-500/30">
            W
          </div>
          <div>
            <h1 className="text-xl font-bold tracking-tight text-white">{title}</h1>
            <p className="text-xs text-slate-400">Laravel 13 + Inertia.js (React + TypeScript)</p>
          </div>
        </div>

        <div className="space-y-4">
          <div className="p-4 rounded-xl bg-slate-800/50 border border-slate-700/50 text-sm">
            <span className="font-semibold text-emerald-400">Status Setup:</span>
            <p className="text-slate-300 mt-1">
              Inertia React TS successfully configured without Breeze starter kits.
            </p>
          </div>

          <div className="grid grid-cols-2 gap-4 text-xs">
            <div className="p-3 rounded-lg bg-slate-800/30 border border-slate-800">
              <span className="text-slate-400">Dynamic Ziggy (@routes):</span>
              <p className="font-mono text-emerald-400 mt-0.5">Active (No ziggy:generate needed)</p>
            </div>
            <div className="p-3 rounded-lg bg-slate-800/30 border border-slate-800">
              <span className="text-slate-400">Auth Status:</span>
              <p className="font-mono text-slate-200 mt-0.5">
                {auth.user ? `${auth.user.name} (${auth.user.role})` : "Guest (Not logged in)"}
              </p>
            </div>
          </div>

          {flash?.success && (
            <div className="p-3 rounded-lg bg-emerald-950/50 border border-emerald-800 text-emerald-300 text-xs">
              {flash.success}
            </div>
          )}

          {flash?.error && (
            <div className="p-3 rounded-lg bg-rose-950/50 border border-rose-800 text-rose-300 text-xs">
              {flash.error}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
