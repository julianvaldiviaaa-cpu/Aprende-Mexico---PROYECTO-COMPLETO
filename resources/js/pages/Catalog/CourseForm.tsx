import { Link, useForm, usePage } from '@inertiajs/react';
import type { SyntheticEvent } from 'react';
import {
    store,
    update,
    index,
} from '@/actions/App/Http/Controllers/Catalog/CourseController';
import { index as categoriesIndex } from '@/actions/App/Http/Controllers/Catalog/CategoryController';
import AppLayout from '@/layouts/app-layout';
import {
    Field,
    inputClassName,
    SubmitButton,
} from '@/components/form-controls';

type Course = {
    id: number;
    title: string;
    summary: string | null;
    description: string | null;
    level: string;
    status: string;
    category_ids: number[];
    attachment_name?: string | null;
};
export default function CourseForm({
    course,
    categories,
}: {
    course: Course | null;
    categories: { id: number; name: string }[];
}) {
    const { auth } = usePage().props;
    const { data, setData, errors, submit, processing } = useForm({
        title: course?.title ?? '',
        summary: course?.summary ?? '',
        description: course?.description ?? '',
        level: course?.level ?? 'beginner',
        status: course?.status ?? 'draft',
        category_ids: course?.category_ids ?? [],
        attachment: null as File | null,
    });
    function handleSubmit(event: SyntheticEvent) {
        event.preventDefault();
        submit(course ? update(course.id) : store(), { preserveScroll: true });
    }
    function toggleCategory(id: number) {
        setData(
            'category_ids',
            data.category_ids.includes(id)
                ? data.category_ids.filter((value) => value !== id)
                : [...data.category_ids, id],
        );
    }
    const categoryError =
        errors.category_ids ||
        Object.entries(errors).find(([key]) =>
            key.startsWith('category_ids.'),
        )?.[1];
    return (
        <AppLayout title={course ? 'Editar curso' : 'Crear curso'}>
            <form
                onSubmit={handleSubmit}
                className="max-w-3xl rounded-2xl border border-stone-200 bg-white p-6 sm:p-8"
            >
                <div className="flex flex-col gap-6">
                    <Field
                        id="title"
                        label="Título del curso"
                        error={errors.title}
                    >
                        <input
                            id="title"
                            name="title"
                            required
                            maxLength={255}
                            value={data.title}
                            onChange={(event) =>
                                setData('title', event.target.value)
                            }
                            className={inputClassName}
                            placeholder="¿Qué vas a enseñar?"
                            aria-invalid={!!errors.title}
                            aria-describedby={
                                errors.title ? 'title-error' : undefined
                            }
                        />
                    </Field>
                    <Field
                        id="summary"
                        label="Resumen (opcional)"
                        error={errors.summary}
                    >
                        <textarea
                            id="summary"
                            name="summary"
                            rows={2}
                            maxLength={1000}
                            value={data.summary}
                            onChange={(event) =>
                                setData('summary', event.target.value)
                            }
                            className={inputClassName}
                            placeholder="Presenta el curso en pocas palabras."
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
                            rows={5}
                            maxLength={20000}
                            value={data.description}
                            onChange={(event) =>
                                setData('description', event.target.value)
                            }
                            className={inputClassName}
                            placeholder="Explica qué aprenderán tus alumnos."
                        />
                    </Field>
                    <div className="grid gap-6 sm:grid-cols-2">
                        <Field id="level" label="Nivel" error={errors.level}>
                            <select
                                id="level"
                                name="level"
                                value={data.level}
                                onChange={(event) =>
                                    setData('level', event.target.value)
                                }
                                className={inputClassName}
                            >
                                <option value="beginner">Principiante</option>
                                <option value="intermediate">Intermedio</option>
                                <option value="advanced">Avanzado</option>
                            </select>
                        </Field>
                        <Field id="status" label="Estado" error={errors.status}>
                            <select
                                id="status"
                                name="status"
                                value={data.status}
                                onChange={(event) =>
                                    setData('status', event.target.value)
                                }
                                className={inputClassName}
                            >
                                <option value="draft">Borrador</option>
                                <option value="published">Publicado</option>
                            </select>
                        </Field>
                    </div>
                    <Field
                        id="attachment"
                        label="Archivo del curso (opcional, máximo 20 MB)"
                        error={errors.attachment}
                    >
                        {course?.attachment_name && (
                            <p className="mb-2 text-sm text-stone-600">
                                Archivo actual: {course.attachment_name}. Si seleccionas otro, se reemplazará.
                            </p>
                        )}
                        <input
                            id="attachment"
                            name="attachment"
                            type="file"
                            accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.zip,.png,.jpg,.jpeg"
                            onChange={(event) =>
                                setData('attachment', event.target.files?.[0] ?? null)
                            }
                            className={inputClassName}
                            aria-invalid={!!errors.attachment}
                        />
                    </Field>
                    <fieldset>
                        <legend className="font-medium">Categorías</legend>
                        <p className="mt-2 text-sm text-stone-600">
                            Selecciona una o varias categorías, hasta un máximo
                            de 10.
                        </p>
                        {categories.length === 0 && (
                            <p className="mt-4 rounded-xl bg-amber-50 p-4 text-sm text-amber-900">
                                Se necesita al menos una categoría para crear un
                                curso.{' '}
                                {auth.is_admin ? (
                                    <Link
                                        href={categoriesIndex()}
                                        className="font-semibold underline"
                                    >
                                        Crear una categoría
                                    </Link>
                                ) : (
                                    'Un administrador debe crear las categorías disponibles.'
                                )}
                            </p>
                        )}
                        <div className="mt-4 grid gap-3 sm:grid-cols-2">
                            {categories.map((category) => (
                                <label
                                    key={category.id}
                                    className="flex items-center gap-3 rounded-xl border border-stone-200 px-4 py-3 text-sm"
                                >
                                    <input
                                        type="checkbox"
                                        name="category_ids[]"
                                        value={category.id}
                                        checked={data.category_ids.includes(
                                            category.id,
                                        )}
                                        onChange={() =>
                                            toggleCategory(category.id)
                                        }
                                        className="size-4 accent-emerald-800"
                                    />
                                    {category.name}
                                </label>
                            ))}
                        </div>
                        {categoryError && (
                            <p
                                role="alert"
                                className="mt-3 text-sm text-red-600"
                            >
                                {categoryError}
                            </p>
                        )}
                    </fieldset>
                    <div className="flex flex-wrap items-center gap-5 border-t border-stone-200 pt-6">
                        <SubmitButton
                            processing={processing || categories.length === 0}
                        >
                            {processing
                                ? 'Guardando…'
                                : course
                                  ? 'Guardar cambios'
                                  : 'Crear curso'}
                        </SubmitButton>
                        <Link
                            href={index()}
                            className="text-sm font-medium text-stone-600 hover:underline"
                        >
                            Volver a cursos
                        </Link>
                    </div>
                </div>
            </form>
        </AppLayout>
    );
}
