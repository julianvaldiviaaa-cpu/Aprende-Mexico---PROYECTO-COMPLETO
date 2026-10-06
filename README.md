# México Aprende: estructura sencilla

# *Trabajando con: Neon Database*

La primera versión se concentra en publicar cursos gratuitos y permitir que un alumno estudie, entregue tareas, responda cuestionarios y consulte su avance. Las instituciones pueden reunir profesores y organizar alumnos en grupos.

## Tamaño de la base

La estructura se redujo de 52 a **19 tablas nuevas**, de 53 a **20 modelos contando User** y de 58 a **24 controladores nuevos**, distribuidos en seis áreas. Hay 20 migraciones nuevas: una amplía users y las demás crean las tablas educativas. Las tablas internas de Laravel se conservan.

```sh
php artisan migrate
```

Las migraciones de la estructura anterior seguían pendientes cuando se hizo la simplificación. Se sustituyeron sus archivos sin modificar ni vaciar la base local. La instalación nueva, actualización desde las tablas iniciales de Laravel y rollback se verifican en bases SQLite de pruebas aisladas.

Para cargar un ejemplo opcional en desarrollo:

```sh
php artisan db:seed --class=EducationalPlatformSeeder
```

El ejemplo crea profesor aprobado, estudiante, institución, curso publicado, módulo, lección, actividad calificada, cuestionario y grupo. No se ejecuta al migrar.

## Cómo se conecta todo

- **Cuenta y docente:** User es la cuenta central. InstructorProfile guarda su presentación pública y aprobación. Un profesor también puede inscribirse a cursos como estudiante.
- **Institución:** Institution tiene propietario y miembros mediante InstitutionMember. Cada curso tiene un profesor responsable; institution_id es opcional para publicar independientemente.
- **Curso:** Course → CourseModule → Lesson. Un curso tiene una categoría opcional. Una lección guarda directamente texto, enlace de video y ruta de un archivo.
- **Aprendizaje:** User → Enrollment → LessonProgress. El porcentaje del curso se calcula con las lecciones obligatorias completadas; no existe una tabla adicional de agregados.
- **Tareas:** Lesson → Activity → ActivitySubmission. La entrega incluye respuesta, archivo, nota y comentarios del docente. Hay una entrega por tarea e inscripción, que puede actualizarse.
- **Cuestionarios:** Lesson → Quiz → Question → QuestionOption. Enrollment → QuizAttempt → QuizAnswer guarda intentos y respuestas. La calificación total vive en el intento.
- **Grupos:** Institution → Classroom. ClassroomMember vincula alumnos y profesores; CourseAssignment asigna un curso al grupo. El seguimiento usa la inscripción del alumno en ese curso, sin una tabla adicional de asignaciones por alumno.

## Decisiones para mantenerlo simple

- Un curso tiene **un profesor responsable y una categoría**; no hay colaboradores, etiquetas ni varias categorías por curso.
- El curso tiene una sola edición editable. No hay versiones, revisión editorial separada ni transferencias de propiedad.
- Las lecciones almacenan contenido directamente. No hay bloques, variantes multimedia, transcodificación ni pistas de subtítulos administradas en tablas.
- Las calificaciones viven en entregas e intentos. No hay Grade, historial de revisiones, rúbricas avanzadas ni una tabla de progreso agregado.
- La aprobación docente y verificación institucional se guardan en el propio perfil. No hay expedientes, documentos, invitaciones ni solicitudes de incorporación.
- Se retiraron constancias, comentarios, reseñas, reportes de abuso, moderación con apelaciones, auditoría, exportaciones, consentimientos y eventos de analítica.
- Los roles institucionales son sencillos y locales. Los roles de grupo distinguen alumno y docente.
- Se retiraron MFA y sus controladores. Laravel conserva sus servicios básicos de sesiones, correo y recuperación de contraseña.
- No hay Python, IA, relaciones polimórficas, claves compuestas entre versiones ni triggers SQL.

Los controladores siguen vacíos. Las pantallas y la lógica de registro, publicación, calificación y seguimiento se implementarán a partir de esta base.

## Relaciones e integridad

Las relaciones usan claves foráneas simples y restricciones únicas para impedir duplicados en inscripciones, membresías, progreso, entregas, intentos y respuestas. Las claves foráneas restringen el borrado físico de registros relacionados. User, InstructorProfile, Institution, Course y Classroom conservan borrado lógico para poder archivarlos sin perder referencias.

Las relaciones `users()` de instituciones y grupos, `students()` del curso y `courses()` del grupo recorren sus tablas intermedias. También se puede acceder al modelo de la asociación para consultar su rol, responsable o fecha límite.

Las reglas de acceso y coherencia se comprobarán en la futura lógica: el alumno debe estar inscrito al curso al que pertenecen su lección, tarea o cuestionario; la pregunta debe pertenecer al examen del intento; las notas deben respetar la puntuación máxima; y el máximo de intentos se valida al iniciar uno. Las claves foráneas simples garantizan que los registros existen, pero no resuelven esas comprobaciones entre varios niveles.

Sin versionado, editar el contenido publicado afecta al curso actual. La lógica futura debe controlar los cambios de actividades y cuestionarios que ya tengan respuestas, y recalcular el progreso si se cambian las lecciones obligatorias. No hay historial de ediciones independiente.

Las rutas de archivos son datos de almacenamiento; las entregas requieren almacenamiento privado y descargas autorizadas. Guardar una ruta o una URL no realiza una carga de archivo ni valida su contenido. `QuestionOption.is_correct`, `QuizAnswer.points_awarded`, las rutas privadas de entregas y los secretos de User se ocultan al serializar. `fillable` tampoco sustituye validación y autorización.

## Modelos y slugs

Todos los modelos conservan `@property`, `@property-read`, casts y relaciones con tipos explícitos. Fechas: CarbonImmutable; decimales: cadenas con dos decimales; respuestas de cuestionario: arrays.

HasSlug crea y actualiza el slug cuando cambia el nombre o título de InstructorProfile, Institution, Category, Course, Lesson o Classroom. La unicidad es por tabla y los duplicados reciben sufijos como -2. Los registros archivados reservan el slug. Las actualizaciones masivas con query builder o los guardados sin eventos no ejecutan este mecanismo; una colisión simultánea es rechazada por el índice único y puede reintentarse en la futura lógica.

## Tablas, columnas y modelos

Todas las tablas siguientes incluyen id autoincremental y created_at / updated_at. **users ya existía**; solo se amplía con status, is_platform_admin, avatar_path y deleted_at. Las demás tablas son nuevas. FK significa clave foránea; nullable significa opcional.

### `users` — `User`

Cuenta central; puede estudiar, enseñar y pertenecer a varias instituciones simultáneamente.

| Columna | Tipo y condiciones | Para qué sirve |
|---|---|---|
| `name` | string | Nombre de la cuenta |
| `email` | string; único | Correo único |
| `email_verified_at` | fecha; nullable | Fecha de verificación del correo |
| `password` | string | Contraseña almacenada como hash |
| `remember_token` | string; nullable | Token de sesión persistente |
| `status` | string; inicial active | active o suspended |
| `is_platform_admin` | boolean; inicial false | Permiso global de administración |
| `avatar_path` | string; nullable | Ruta del avatar |
| `deleted_at` | fecha; nullable | Borrado lógico de la cuenta |

Relaciones:

- `instructorProfile()` → `InstructorProfile` (HasOne): obtiene el perfil único asociado a esta cuenta.
- `reviewedInstructorProfiles()` → `InstructorProfile` (HasMany): perfiles cuya aprobación revisó esta cuenta.
- `ownedInstitutions()` → `Institution` (HasMany): instituciones de las que es propietario.
- `institutionMemberships()` → `InstitutionMember` (HasMany): membresías y roles institucionales de esta cuenta.
- `taughtCourses()` → `Course` (HasMany): cursos de los que es profesor responsable.
- `enrollments()` → `Enrollment` (HasMany): inscripciones del alumno, con su estado y fechas.
- `gradedActivitySubmissions()` → `ActivitySubmission` (HasMany): entregas que calificó como docente.
- `createdClassrooms()` → `Classroom` (HasMany): grupos que organizó.
- `classroomMemberships()` → `ClassroomMember` (HasMany): participaciones en grupos como alumno o docente.
- `courseAssignments()` → `CourseAssignment` (HasMany): cursos que asignó a grupos.
- `institutions()` → `Institution` (BelongsToMany): instituciones a las que pertenece mediante `institution_members`.
- `classrooms()` → `Classroom` (BelongsToMany): grupos en los que participa mediante `classroom_members`.
- `enrolledCourses()` → `Course` (BelongsToMany): cursos que estudia mediante `enrollments`.

Archivo del modelo: `app/Models/User.php`. Factory: `database/factories/UserFactory.php`.

### `instructor_profiles` — `InstructorProfile`

Perfil docente público y aprobación sencilla para enseñar.

| Columna | Tipo y condiciones | Para qué sirve |
|---|---|---|
| `user_id` | FK → users; único | Referencia a users |
| `display_name` | string | Nombre público del profesor |
| `slug` | string; único | Identificador legible generado desde display_name |
| `biography` | texto; nullable | Presentación y experiencia docente |
| `status` | string; inicial pending | pending, approved o suspended |
| `reviewed_by` | FK → users; nullable | Referencia a users |
| `reviewed_at` | fecha; nullable | Fecha de aprobación o revisión |
| `deleted_at` | fecha; nullable | Borrado lógico |

Relaciones:

- `user()` → `User` (BelongsTo): cuenta del profesor mediante `user_id`.
- `reviewer()` → `User` (BelongsTo): cuenta que revisó su aprobación mediante `reviewed_by`.

Archivo del modelo: `app/Models/InstructorProfile.php`. Factory: `database/factories/InstructorProfileFactory.php`.

### `institutions` — `Institution`

Escuela u organización propietaria de cursos y grupos.

| Columna | Tipo y condiciones | Para qué sirve |
|---|---|---|
| `owner_user_id` | FK → users | Referencia a users |
| `name` | string | Nombre institucional |
| `slug` | string; único | Identificador legible generado desde name |
| `description` | texto; nullable | Presentación de la institución |
| `logo_path` | string; nullable | Ruta del logotipo |
| `is_verified` | boolean; inicial false | Verificación manual de la institución |
| `deleted_at` | fecha; nullable | Borrado lógico |

Relaciones:

- `owner()` → `User` (BelongsTo): propietario de la institución mediante `owner_user_id`.
- `members()` → `InstitutionMember` (HasMany): integrantes con su rol y fecha de incorporación.
- `courses()` → `Course` (HasMany): cursos publicados para esta institución.
- `classrooms()` → `Classroom` (HasMany): grupos escolares de la institución.
- `users()` → `User` (BelongsToMany): cuentas de sus integrantes mediante `institution_members`.

Archivo del modelo: `app/Models/Institution.php`. Factory: `database/factories/InstitutionFactory.php`.

### `institution_members` — `InstitutionMember`

Miembro con un rol local en una institución.

| Columna | Tipo y condiciones | Para qué sirve |
|---|---|---|
| `institution_id` | FK → institutions | Referencia a institutions |
| `user_id` | FK → users | Referencia a users |
| `role` | string; inicial member | administrator, instructor o member |
| `joined_at` | fecha | Fecha de incorporación |

Relaciones:

- `institution()` → `Institution` (BelongsTo): institución a la que pertenece el registro mediante `institution_id`.
- `user()` → `User` (BelongsTo): cuenta asociada a esta participación mediante `user_id`.

No se repite: `institution_id + user_id`.

Archivo del modelo: `app/Models/InstitutionMember.php`. Factory: `database/factories/InstitutionMemberFactory.php`.

### `categories` — `Category`

Clasificación sencilla de cursos por materia.

| Columna | Tipo y condiciones | Para qué sirve |
|---|---|---|
| `name` | string | Nombre de la materia |
| `slug` | string; único | Identificador legible generado desde name |
| `description` | texto; nullable | Descripción de la categoría |

Relaciones:

- `courses()` → `Course` (HasMany): cursos clasificados en esta categoría.

Archivo del modelo: `app/Models/Category.php`. Factory: `database/factories/CategoryFactory.php`.

### `courses` — `Course`

Curso independiente o institucional con un profesor responsable y una categoría.

| Columna | Tipo y condiciones | Para qué sirve |
|---|---|---|
| `instructor_id` | FK → users | Referencia a users |
| `institution_id` | FK → institutions; nullable | Referencia a institutions |
| `category_id` | FK → categories; nullable | Referencia a categories |
| `title` | string | Título del curso |
| `slug` | string; único | Identificador legible generado desde title |
| `summary` | texto; nullable | Resumen para el catálogo |
| `description` | texto largo; nullable | Objetivos, requisitos y descripción completa |
| `cover_path` | string; nullable | Ruta de la portada |
| `level` | string; inicial beginner | beginner, intermediate o advanced |
| `status` | string; inicial draft | draft, published o archived |
| `published_at` | fecha; nullable | Fecha de publicación |
| `deleted_at` | fecha; nullable | Borrado lógico |

Relaciones:

- `instructor()` → `User` (BelongsTo): profesor responsable mediante `instructor_id`.
- `institution()` → `Institution` (BelongsTo): institución que ofrece el curso, si corresponde, mediante `institution_id`.
- `category()` → `Category` (BelongsTo): clasificación temática opcional mediante `category_id`.
- `modules()` → `CourseModule` (HasMany): secciones que organizan las lecciones.
- `enrollments()` → `Enrollment` (HasMany): inscripciones con las que se consulta el avance de cada alumno.
- `assignments()` → `CourseAssignment` (HasMany): asignaciones de este curso a grupos y sus fechas límite.
- `students()` → `User` (BelongsToMany): alumnos inscritos mediante `enrollments`.
- `classrooms()` → `Classroom` (BelongsToMany): grupos que deben estudiar el curso mediante `course_assignments`.

Archivo del modelo: `app/Models/Course.php`. Factory: `database/factories/CourseFactory.php`.

### `course_modules` — `CourseModule`

Sección ordenada del curso; no existen versiones separadas.

| Columna | Tipo y condiciones | Para qué sirve |
|---|---|---|
| `course_id` | FK → courses | Referencia a courses |
| `title` | string | Título del módulo |
| `position` | integer; inicial 0 | Orden dentro del curso |

Relaciones:

- `course()` → `Course` (BelongsTo): curso al que pertenece el registro mediante `course_id`.
- `lessons()` → `Lesson` (HasMany): lecciones que forman esta sección del curso.

Archivo del modelo: `app/Models/CourseModule.php`. Factory: `database/factories/CourseModuleFactory.php`.

### `lessons` — `Lesson`

Lección con texto, un video y un recurso adjunto, sin editor de bloques ni gestor de medios.

| Columna | Tipo y condiciones | Para qué sirve |
|---|---|---|
| `course_module_id` | FK → course_modules | Referencia a course_modules |
| `title` | string | Título de la lección |
| `slug` | string; único | Identificador legible generado desde title |
| `content` | texto largo; nullable | Texto de la lección |
| `video_url` | string; nullable | URL de video externo o de reproducción |
| `attachment_path` | string; nullable | Ruta de un recurso descargable |
| `position` | integer; inicial 0 | Orden dentro del módulo |
| `is_required` | boolean; inicial true | La lección cuenta para completar el curso |

Relaciones:

- `module()` → `CourseModule` (BelongsTo): sección a la que pertenece la lección mediante `course_module_id`.
- `progressRecords()` → `LessonProgress` (HasMany): avance de los alumnos en esta lección.
- `activities()` → `Activity` (HasMany): tareas propuestas para practicar su contenido.
- `quizzes()` → `Quiz` (HasMany): cuestionarios que evalúan su contenido.

Archivo del modelo: `app/Models/Lesson.php`. Factory: `database/factories/LessonFactory.php`.

### `enrollments` — `Enrollment`

Inscripción gratuita única del estudiante a un curso.

| Columna | Tipo y condiciones | Para qué sirve |
|---|---|---|
| `user_id` | FK → users | Referencia a users |
| `course_id` | FK → courses | Referencia a courses |
| `status` | string; inicial active | active, completed o withdrawn |
| `enrolled_at` | fecha | Fecha de inscripción |
| `completed_at` | fecha; nullable | Fecha de finalización |

Relaciones:

- `user()` → `User` (BelongsTo): cuenta asociada a esta participación mediante `user_id`.
- `course()` → `Course` (BelongsTo): curso al que pertenece el registro mediante `course_id`.
- `lessonProgress()` → `LessonProgress` (HasMany): lecciones completadas y posición de reproducción de este alumno.
- `activitySubmissions()` → `ActivitySubmission` (HasMany): tareas entregadas por el alumno en este curso.
- `quizAttempts()` → `QuizAttempt` (HasMany): intentos de evaluación del alumno en este curso.

No se repite: `user_id + course_id`.

Archivo del modelo: `app/Models/Enrollment.php`. Factory: `database/factories/EnrollmentFactory.php`.

### `lesson_progress` — `LessonProgress`

Avance guardado por lección; el progreso total se calcula desde estas filas.

| Columna | Tipo y condiciones | Para qué sirve |
|---|---|---|
| `lesson_id` | FK → lessons | Referencia a lessons |
| `enrollment_id` | FK → enrollments | Referencia a enrollments |
| `is_completed` | boolean; inicial false | Lección terminada |
| `last_position_seconds` | integer; inicial 0 | Última posición del video |
| `completed_at` | fecha; nullable | Fecha de finalización de la lección |

Relaciones:

- `lesson()` → `Lesson` (BelongsTo): lección cuyo contenido se estudia o evalúa mediante `lesson_id`.
- `enrollment()` → `Enrollment` (BelongsTo): inscripción que identifica al alumno y su curso mediante `enrollment_id`.

No se repite: `enrollment_id + lesson_id`.

Archivo del modelo: `app/Models/LessonProgress.php`. Factory: `database/factories/LessonProgressFactory.php`.

### `activities` — `Activity`

Tarea de una lección con respuesta escrita o archivo y calificación manual.

| Columna | Tipo y condiciones | Para qué sirve |
|---|---|---|
| `lesson_id` | FK → lessons | Referencia a lessons |
| `title` | string | Nombre de la tarea |
| `instructions` | texto; nullable | Instrucciones para resolverla |
| `type` | string; inicial text | text o file |
| `max_points` | decimal(10,2); inicial 100 | Puntos máximos |
| `is_required` | boolean; inicial true | La tarea es obligatoria |

Relaciones:

- `lesson()` → `Lesson` (BelongsTo): lección cuyo contenido se estudia o evalúa mediante `lesson_id`.
- `submissions()` → `ActivitySubmission` (HasMany): respuestas de los alumnos a esta tarea, con sus calificaciones.

Archivo del modelo: `app/Models/Activity.php`. Factory: `database/factories/ActivityFactory.php`.

### `activity_submissions` — `ActivitySubmission`

Entrega única por tarea e inscripción; incluye directamente nota y retroalimentación.

| Columna | Tipo y condiciones | Para qué sirve |
|---|---|---|
| `activity_id` | FK → activities | Referencia a activities |
| `enrollment_id` | FK → enrollments | Referencia a enrollments |
| `response` | texto; nullable | Respuesta escrita del alumno |
| `attachment_path` | string; nullable | Ruta privada del archivo entregado |
| `status` | string; inicial draft | draft, submitted o graded |
| `score` | decimal(10,2); nullable | Nota de la entrega, de cero a max_points de la actividad |
| `feedback` | texto; nullable | Comentarios del docente |
| `submitted_at` | fecha; nullable | Fecha de envío |
| `graded_by` | FK → users; nullable | Referencia a users |
| `graded_at` | fecha; nullable | Fecha de calificación |

Relaciones:

- `activity()` → `Activity` (BelongsTo): tarea a la que responde la entrega mediante `activity_id`.
- `enrollment()` → `Enrollment` (BelongsTo): inscripción que identifica al alumno y su curso mediante `enrollment_id`.
- `grader()` → `User` (BelongsTo): docente que calificó la entrega mediante `graded_by`.

No se repite: `activity_id + enrollment_id`.

Archivo del modelo: `app/Models/ActivitySubmission.php`. Factory: `database/factories/ActivitySubmissionFactory.php`.

### `quizzes` — `Quiz`

Cuestionario de una lección con preguntas objetivas y número limitado de intentos.

| Columna | Tipo y condiciones | Para qué sirve |
|---|---|---|
| `lesson_id` | FK → lessons | Referencia a lessons |
| `title` | string | Nombre del cuestionario |
| `instructions` | texto; nullable | Instrucciones al estudiante |
| `passing_score` | decimal(10,2); inicial 60 | Porcentaje mínimo de aprobación |
| `max_attempts` | integer; inicial 1 | Máximo de intentos |
| `is_required` | boolean; inicial true | Debe aprobarse para completar el curso |

Relaciones:

- `lesson()` → `Lesson` (BelongsTo): lección cuyo contenido se estudia o evalúa mediante `lesson_id`.
- `questions()` → `Question` (HasMany): preguntas que componen el cuestionario.
- `attempts()` → `QuizAttempt` (HasMany): participaciones de los alumnos con sus resultados.

Archivo del modelo: `app/Models/Quiz.php`. Factory: `database/factories/QuizFactory.php`.

### `questions` — `Question`

Pregunta de opción múltiple o verdadero/falso.

| Columna | Tipo y condiciones | Para qué sirve |
|---|---|---|
| `quiz_id` | FK → quizzes | Referencia a quizzes |
| `type` | string; inicial multiple_choice | multiple_choice o true_false |
| `prompt` | texto | Enunciado de la pregunta |
| `points` | decimal(10,2); inicial 1 | Valor de la pregunta |
| `position` | integer; inicial 0 | Orden en el cuestionario |

Relaciones:

- `quiz()` → `Quiz` (BelongsTo): cuestionario al que pertenece el registro mediante `quiz_id`.
- `options()` → `QuestionOption` (HasMany): opciones que puede seleccionar el alumno y su corrección.
- `answers()` → `QuizAnswer` (HasMany): respuestas recibidas para esta pregunta en distintos intentos.

Archivo del modelo: `app/Models/Question.php`. Factory: `database/factories/QuestionFactory.php`.

### `question_options` — `QuestionOption`

Respuesta posible de una pregunta; la solución se oculta por defecto.

| Columna | Tipo y condiciones | Para qué sirve |
|---|---|---|
| `question_id` | FK → questions | Referencia a questions |
| `content` | texto | Texto de la opción |
| `is_correct` | boolean; inicial false | Es la opción correcta; oculta al serializar |
| `position` | integer; inicial 0 | Orden de la opción |

Relaciones:

- `question()` → `Question` (BelongsTo): pregunta a la que corresponde la opción o respuesta mediante `question_id`.

Archivo del modelo: `app/Models/QuestionOption.php`. Factory: `database/factories/QuestionOptionFactory.php`.

### `quiz_attempts` — `QuizAttempt`

Intento del alumno; conserva respuestas, resultado y fechas.

| Columna | Tipo y condiciones | Para qué sirve |
|---|---|---|
| `quiz_id` | FK → quizzes | Referencia a quizzes |
| `enrollment_id` | FK → enrollments | Referencia a enrollments |
| `attempt_number` | integer; inicial 1 | Número consecutivo de intento |
| `status` | string; inicial in_progress | in_progress o submitted |
| `score` | decimal(10,2); nullable | Puntos obtenidos en el intento |
| `max_score` | decimal(10,2); nullable | Puntos totales posibles al contestarlo |
| `is_passed` | boolean; nullable | Resultado de aprobación |
| `started_at` | fecha | Inicio del intento |
| `submitted_at` | fecha; nullable | Cierre del intento |

Relaciones:

- `quiz()` → `Quiz` (BelongsTo): cuestionario al que pertenece el registro mediante `quiz_id`.
- `enrollment()` → `Enrollment` (BelongsTo): inscripción que identifica al alumno y su curso mediante `enrollment_id`.
- `answers()` → `QuizAnswer` (HasMany): respuestas que forman este intento y permiten calcular su nota.

No se repite: `quiz_id + enrollment_id + attempt_number`.

Archivo del modelo: `app/Models/QuizAttempt.php`. Factory: `database/factories/QuizAttemptFactory.php`.

### `quiz_answers` — `QuizAnswer`

Respuesta única a una pregunta dentro de un intento.

| Columna | Tipo y condiciones | Para qué sirve |
|---|---|---|
| `question_id` | FK → questions | Referencia a questions |
| `quiz_attempt_id` | FK → quiz_attempts | Referencia a quiz_attempts |
| `response` | json; nullable | Respuesta estructurada, por ejemplo el ID de opción seleccionada |
| `points_awarded` | decimal(10,2); nullable | Puntos otorgados a esta respuesta |

Relaciones:

- `question()` → `Question` (BelongsTo): pregunta a la que corresponde la opción o respuesta mediante `question_id`.
- `attempt()` → `QuizAttempt` (BelongsTo): participación del alumno en la que respondió mediante `quiz_attempt_id`.

No se repite: `quiz_attempt_id + question_id`.

Archivo del modelo: `app/Models/QuizAnswer.php`. Factory: `database/factories/QuizAnswerFactory.php`.

### `classrooms` — `Classroom`

Grupo de estudiantes y docentes dentro de una institución.

| Columna | Tipo y condiciones | Para qué sirve |
|---|---|---|
| `institution_id` | FK → institutions | Referencia a institutions |
| `created_by` | FK → users | Referencia a users |
| `name` | string | Nombre del grupo |
| `slug` | string; único | Identificador legible generado desde name |
| `description` | texto; nullable | Descripción o periodo escolar |
| `status` | string; inicial active | active o archived |
| `deleted_at` | fecha; nullable | Borrado lógico |

Relaciones:

- `institution()` → `Institution` (BelongsTo): institución a la que pertenece el registro mediante `institution_id`.
- `creator()` → `User` (BelongsTo): cuenta que organizó el grupo mediante `created_by`.
- `members()` → `ClassroomMember` (HasMany): participaciones con rol de alumno o docente.
- `assignments()` → `CourseAssignment` (HasMany): cursos asignados al grupo con responsable y fecha límite.
- `users()` → `User` (BelongsToMany): alumnos y docentes del grupo mediante `classroom_members`.
- `courses()` → `Course` (BelongsToMany): cursos que estudia el grupo mediante `course_assignments`.

Archivo del modelo: `app/Models/Classroom.php`. Factory: `database/factories/ClassroomFactory.php`.

### `classroom_members` — `ClassroomMember`

Alumno o docente participante de un grupo.

| Columna | Tipo y condiciones | Para qué sirve |
|---|---|---|
| `classroom_id` | FK → classrooms | Referencia a classrooms |
| `user_id` | FK → users | Referencia a users |
| `role` | string; inicial student | student o teacher |
| `joined_at` | fecha | Fecha de incorporación |

Relaciones:

- `classroom()` → `Classroom` (BelongsTo): grupo al que pertenece esta participación o asignación mediante `classroom_id`.
- `user()` → `User` (BelongsTo): cuenta asociada a esta participación mediante `user_id`.

No se repite: `classroom_id + user_id`.

Archivo del modelo: `app/Models/ClassroomMember.php`. Factory: `database/factories/ClassroomMemberFactory.php`.

### `course_assignments` — `CourseAssignment`

Curso asignado a un grupo; conserva responsable y fecha límite opcional.

| Columna | Tipo y condiciones | Para qué sirve |
|---|---|---|
| `classroom_id` | FK → classrooms | Referencia a classrooms |
| `course_id` | FK → courses | Referencia a courses |
| `assigned_by` | FK → users | Referencia a users |
| `due_at` | fecha; nullable | Fecha límite opcional |

Relaciones:

- `classroom()` → `Classroom` (BelongsTo): grupo al que pertenece esta participación o asignación mediante `classroom_id`.
- `course()` → `Course` (BelongsTo): curso al que pertenece el registro mediante `course_id`.
- `assigner()` → `User` (BelongsTo): cuenta que asignó el curso al grupo mediante `assigned_by`.

No se repite: `classroom_id + course_id`.

Archivo del modelo: `app/Models/CourseAssignment.php`. Factory: `database/factories/CourseAssignmentFactory.php`.

## Controladores vacíos por área

Los archivos conservan la plantilla de Artisan, sin métodos, rutas ni vistas.

- `app/Http/Controllers/Identity/`: `UserController`, `InstructorProfileController`, `SessionController`, `RegistrationController`, `PasswordResetController`, `EmailVerificationController`.
- `app/Http/Controllers/Institutions/`: `InstitutionController`, `InstitutionMemberController`.
- `app/Http/Controllers/Catalog/`: `CategoryController`, `CourseController`, `CourseModuleController`, `LessonController`.
- `app/Http/Controllers/Learning/`: `EnrollmentController`, `LessonProgressController`.
- `app/Http/Controllers/Assessment/`: `ActivityController`, `ActivitySubmissionController`, `QuizController`, `QuestionController`, `QuestionOptionController`, `QuizAttemptController`, `QuizAnswerController`.
- `app/Http/Controllers/Classrooms/`: `ClassroomController`, `ClassroomMemberController`, `CourseAssignmentController`.

## Infraestructura de Laravel conservada

| Tabla | Columnas | Uso |
|---|---|---|
| password_reset_tokens | email PK, token, created_at nullable | Recuperación de contraseña |
| sessions | id PK, user_id nullable indexado, ip_address nullable, user_agent nullable, payload, last_activity indexado | Sesión de la cuenta; la migración original no declara FK |
| cache | key PK, value, expiration indexado | Caché de Laravel |
| cache_locks | key PK, owner, expiration indexado | Bloqueos de caché |
| jobs | id PK, queue indexado, payload, attempts, reserved_at nullable, available_at, created_at | Trabajos de Laravel en segundo plano |
| job_batches | id PK, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options nullable, cancelled_at nullable, created_at, finished_at nullable | Lotes de trabajos |
| failed_jobs | id PK, uuid único, connection, queue, payload, exception, failed_at | Fallos de trabajos |
| migrations | id PK, migration, batch | Control interno de migraciones |

Estas tablas pertenecen al framework y no requieren modelos educativos propios. La simplificación conserva la recuperación de contraseña, sesiones y trabajos habituales sin crear sistemas adicionales.
