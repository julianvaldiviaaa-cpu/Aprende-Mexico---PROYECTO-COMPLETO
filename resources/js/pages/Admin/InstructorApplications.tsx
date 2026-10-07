import { Link, useForm } from '@inertiajs/react';
import {
    approve,
    reject,
    index,
} from '@/actions/App/Http/Controllers/Identity/InstructorProfileController';
import AppLayout from '@/layouts/app-layout';
import { Pagination } from '@/components/form-controls';
import type { PageLink } from '@/components/form-controls';

type Profile = {
    id: number;
    display_name: string;
    biography: string | null;
    status: string;
    user: { name: string; email: string; status: string };
};
function ReviewControls({ profile }: { profile: Profile }) {
    const { submit, processing, errors } = useForm<Record<string, string>>({});
    return (
        <div>
            <div className="mt-5 flex gap-3">
                <button
                    type="button"
                    disabled={processing}
                    onClick={() =>
                        submit(approve(profile.id), { preserveScroll: true })
                    }
                    className="rounded-xl bg-emerald-800 px-5 py-3 font-semibold text-white disabled:opacity-50"
                >
                    {processing ? 'Procesando…' : 'Aprobar'}
                </button>
                <button
                    type="button"
                    disabled={processing}
                    onClick={() =>
                        submit(reject(profile.id), { preserveScroll: true })
                    }
                    className="rounded-xl border border-stone-300 px-5 py-3 font-semibold hover:bg-stone-50 disabled:opacity-50"
                >
                    Rechazar
                </button>
            </div>
            {errors.review && (
                <p role="alert" className="mt-3 text-sm text-red-600">
                    {errors.review}
                </p>
            )}
        </div>
    );
}
export default function InstructorApplications({
    profiles,
    status,
}: {
    profiles: { data: Profile[]; links: PageLink[] };
    status: string;
}) {
    const filters = [
        { value: 'pending', label: 'Pendientes' },
        { value: 'approved', label: 'Aprobadas' },
        { value: 'rejected', label: 'Rechazadas' },
    ];
    return (
        <AppLayout title="Solicitudes de instructor">
            <nav
                aria-label="Estado de solicitudes"
                className="mb-7 flex flex-wrap gap-3"
            >
                {filters.map((filter) => (
                    <Link
                        key={filter.value}
                        href={index({ query: { status: filter.value } })}
                        aria-current={
                            status === filter.value ? 'page' : undefined
                        }
                        className={
                            'rounded-full px-5 py-2 text-sm font-medium ' +
                            (status === filter.value
                                ? 'bg-emerald-800 text-white'
                                : 'border border-stone-300 bg-white')
                        }
                    >
                        {filter.label}
                    </Link>
                ))}
            </nav>
            {profiles.data.length === 0 && (
                <p className="rounded-2xl border border-dashed border-stone-300 p-10 text-center text-stone-600">
                    No hay solicitudes en este estado.
                </p>
            )}
            <div className="grid gap-5 md:grid-cols-2">
                {profiles.data.map((profile) => (
                    <article
                        key={profile.id}
                        className="rounded-2xl border border-stone-200 bg-white p-6"
                    >
                        <h2 className="text-xl font-semibold">
                            {profile.display_name}
                        </h2>
                        <p className="mt-2 text-sm text-stone-500">
                            {profile.user.name} · {profile.user.email}
                        </p>
                        <p className="mt-4 leading-7 whitespace-pre-wrap text-stone-700">
                            {profile.biography || 'Sin presentación adicional.'}
                        </p>
                        {profile.status === 'pending' && (
                            <ReviewControls profile={profile} />
                        )}
                    </article>
                ))}
            </div>
            <Pagination links={profiles.links} />
        </AppLayout>
    );
}
