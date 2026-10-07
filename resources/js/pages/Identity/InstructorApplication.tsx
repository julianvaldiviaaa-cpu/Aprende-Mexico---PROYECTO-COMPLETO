import { Link, useForm, usePage } from '@inertiajs/react';
import type { SyntheticEvent } from 'react';
import { store } from '@/actions/App/Http/Controllers/Identity/InstructorProfileController';
import { index as courses } from '@/actions/App/Http/Controllers/Catalog/CourseController';
import AppLayout from '@/layouts/app-layout';
import {
    Field,
    inputClassName,
    SubmitButton,
} from '@/components/form-controls';

type Profile = {
    display_name: string;
    biography: string | null;
    status: string;
};
export default function InstructorApplication({
    profile,
}: {
    profile: Profile | null;
}) {
    const { auth } = usePage().props;
    const { data, setData, errors, submit, processing } = useForm({
        display_name: profile?.display_name ?? auth.user?.name ?? '',
        biography: profile?.biography ?? '',
    });
    function handleSubmit(event: SyntheticEvent) {
        event.preventDefault();
        submit(store(), { preserveScroll: true });
    }
    const canApply =
        auth.user?.role === 'user' &&
        (!profile || profile.status === 'rejected');
    return (
        <AppLayout title="Comparte lo que sabes">
            <div className="max-w-2xl rounded-2xl border border-stone-200 bg-white p-6 sm:p-8">
                <p className="mb-6 leading-7 text-stone-600">
                    Solicita un perfil de instructor para crear tus propios
                    cursos. Un administrador revisará tu presentación.
                </p>
                {profile?.status === 'pending' && (
                    <p
                        role="status"
                        className="rounded-xl bg-amber-50 p-5 text-amber-900"
                    >
                        Tu solicitud está pendiente. Podrás crear cursos cuando
                        sea aprobada.
                    </p>
                )}
                {profile?.status === 'rejected' && (
                    <p className="mb-6 rounded-xl bg-red-50 p-5 text-red-800">
                        Tu solicitud fue rechazada. Puedes actualizar tu
                        presentación y volver a enviarla.
                    </p>
                )}
                {auth.can_create_courses && (
                    <p className="rounded-xl bg-emerald-50 p-5 text-emerald-900">
                        Ya puedes enseñar en México Aprende.{' '}
                        <Link
                            href={courses()}
                            className="font-semibold underline"
                        >
                            Ir a cursos
                        </Link>
                    </p>
                )}
                {canApply && (
                    <form
                        onSubmit={handleSubmit}
                        className="flex flex-col gap-6"
                    >
                        <Field
                            id="display_name"
                            label="Nombre público"
                            error={errors.display_name}
                        >
                            <input
                                id="display_name"
                                name="display_name"
                                required
                                maxLength={255}
                                value={data.display_name}
                                onChange={(event) =>
                                    setData('display_name', event.target.value)
                                }
                                className={inputClassName}
                                aria-invalid={!!errors.display_name}
                                aria-describedby={
                                    errors.display_name
                                        ? 'display_name-error'
                                        : undefined
                                }
                            />
                        </Field>
                        <Field
                            id="biography"
                            label="Cuéntanos sobre ti y lo que quieres enseñar"
                            error={errors.biography}
                        >
                            <textarea
                                id="biography"
                                name="biography"
                                rows={6}
                                maxLength={5000}
                                value={data.biography}
                                onChange={(event) =>
                                    setData('biography', event.target.value)
                                }
                                className={inputClassName}
                            />
                        </Field>
                        <SubmitButton processing={processing}>
                            {processing ? 'Enviando…' : 'Enviar solicitud'}
                        </SubmitButton>
                    </form>
                )}
            </div>
        </AppLayout>
    );
}
