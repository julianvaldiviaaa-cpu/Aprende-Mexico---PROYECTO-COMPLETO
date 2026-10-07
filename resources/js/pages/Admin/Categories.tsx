import { useForm } from '@inertiajs/react';
import type { SyntheticEvent } from 'react';
import { store } from '@/actions/App/Http/Controllers/Catalog/CategoryController';
import AppLayout from '@/layouts/app-layout';
import {
    Field,
    inputClassName,
    Pagination,
    SubmitButton,
} from '@/components/form-controls';
import type { PageLink } from '@/components/form-controls';

type Category = {
    id: number;
    name: string;
    description: string | null;
    courses_count: number;
};
export default function Categories({
    categories,
}: {
    categories: { data: Category[]; links: PageLink[] };
}) {
    const { data, setData, errors, submit, processing, reset } = useForm({
        name: '',
        description: '',
    });
    function handleSubmit(event: SyntheticEvent) {
        event.preventDefault();
        submit(store(), { preserveScroll: true, onSuccess: () => reset() });
    }
    return (
        <AppLayout title="Categorías">
            <div className="grid items-start gap-8 lg:grid-cols-[1fr_1.5fr]">
                <form
                    onSubmit={handleSubmit}
                    className="flex flex-col gap-5 rounded-2xl border border-stone-200 bg-white p-6"
                >
                    <h2 className="text-xl font-semibold">Crear categoría</h2>
                    <p className="text-sm leading-6 text-stone-600">
                        Organiza los cursos por temas para que sea más fácil
                        encontrarlos.
                    </p>
                    <Field id="name" label="Nombre" error={errors.name}>
                        <input
                            id="name"
                            name="name"
                            required
                            maxLength={255}
                            value={data.name}
                            onChange={(event) =>
                                setData('name', event.target.value)
                            }
                            className={inputClassName}
                            aria-invalid={!!errors.name}
                            aria-describedby={
                                errors.name ? 'name-error' : undefined
                            }
                        />
                    </Field>
                    <Field
                        id="description"
                        label="Descripción (opcional)"
                        error={errors.description}
                    >
                        <textarea
                            id="description"
                            name="description"
                            rows={4}
                            maxLength={2000}
                            value={data.description}
                            onChange={(event) =>
                                setData('description', event.target.value)
                            }
                            className={inputClassName}
                        />
                    </Field>
                    <SubmitButton processing={processing}>
                        {processing ? 'Guardando…' : 'Crear categoría'}
                    </SubmitButton>
                </form>
                <section aria-label="Categorías existentes">
                    {categories.data.length === 0 && (
                        <p className="rounded-2xl border border-dashed border-stone-300 p-8 text-stone-600">
                            Todavía no hay categorías. Crea la primera para
                            empezar a organizar los cursos.
                        </p>
                    )}
                    <div className="flex flex-col gap-4">
                        {categories.data.map((category) => (
                            <article
                                key={category.id}
                                className="rounded-2xl border border-stone-200 bg-white p-5"
                            >
                                <div className="flex items-start justify-between gap-3">
                                    <h2 className="font-semibold">
                                        {category.name}
                                    </h2>
                                    <span className="text-sm text-stone-500">
                                        {category.courses_count} cursos
                                    </span>
                                </div>
                                {category.description && (
                                    <p className="mt-2 text-sm leading-6 whitespace-pre-wrap text-stone-600">
                                        {category.description}
                                    </p>
                                )}
                            </article>
                        ))}
                    </div>
                    <Pagination links={categories.links} />
                </section>
            </div>
        </AppLayout>
    );
}
