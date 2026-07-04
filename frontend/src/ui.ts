export const input =
    "w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500";
export const label = "mb-1 block text-sm font-medium text-slate-700";
export const btnPrimary =
    "rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2";
export const btnSecondary =
    "rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50";
export const btnDanger = "text-sm font-medium text-red-600 hover:text-red-800";
export const link = "text-sm font-medium text-indigo-600 hover:text-indigo-800";
export const errorText = "text-sm text-red-600";
export const fieldError = "mt-1 text-xs text-red-600";
export const card = "rounded-lg border border-slate-200 bg-white p-6 shadow-sm";

const badgeColors: Record<string, string> = {
    ok: "bg-green-100 text-green-700",
    success: "bg-green-100 text-green-700",
    alerte: "bg-amber-100 text-amber-700",
    warning: "bg-amber-100 text-amber-700",
    rupture: "bg-red-100 text-red-700",
    danger: "bg-red-100 text-red-700",
    perime: "bg-red-100 text-red-700",
    proche_peremption: "bg-amber-100 text-amber-700",
    secondary: "bg-slate-100 text-slate-600",
};

export function badgeClass(variant: string | null | undefined): string {
    return `rounded-full px-2 py-0.5 text-xs font-medium ${badgeColors[variant ?? "secondary"] ?? badgeColors.secondary}`;
}
