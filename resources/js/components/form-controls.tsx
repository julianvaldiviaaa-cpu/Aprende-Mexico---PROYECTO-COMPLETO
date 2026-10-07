import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';

export const inputClassName =
    'w-full rounded-xl border border-stone-300 bg-white px-4 py-3 text-stone-900 outline-none transition focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/20 disabled:opacity-60';

export function Field({
    id,
    label,
    error,
    children,
}: {
    id: string;
    label: string;
    error?: string;
    children: ReactNode;
}) {
    return (
        <div className="flex flex-col gap-2">
            <label htmlFor={id} className="text-sm font-medium">
                {label}
            </label>
            {children}
            {error && (
                <p
                    id={id + '-error'}
                    role="alert"
                    className="text-sm text-red-600"
                >
                    {error}
                </p>
            )}
        </div>
    );
}

export function SubmitButton({
    processing,
    children,
}: {
    processing: boolean;
    children: ReactNode;
}) {
    return (
        <button
            type="submit"
            disabled={processing}
            className="rounded-xl bg-emerald-800 px-6 py-3 font-semibold text-white transition hover:bg-emerald-900 disabled:cursor-not-allowed disabled:opacity-50"
        >
            {children}
        </button>
    );
}

export type PageLink = { url: string | null; label: string; active: boolean };

export function Pagination({ links }: { links: PageLink[] }) {
    return (
        <nav aria-label="Páginas" className="mt-8 flex flex-wrap gap-2">
            {links.map((link, index) => {
                const label = link.label.includes('Previous')
                    ? 'Anterior'
                    : link.label.includes('Next')
                      ? 'Siguiente'
                      : link.label;
                return link.url ? (
                    <Link
                        key={index}
                        href={link.url}
                        aria-current={link.active ? 'page' : undefined}
                        className={
                            'rounded-lg border px-3 py-2 text-sm ' +
                            (link.active
                                ? 'border-emerald-800 bg-emerald-800 text-white'
                                : 'border-stone-200 bg-white hover:bg-stone-100')
                        }
                    >
                        {label}
                    </Link>
                ) : (
                    <span
                        key={index}
                        className="px-3 py-2 text-sm text-stone-400"
                    >
                        {label}
                    </span>
                );
            })}
        </nav>
    );
}
