<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug - Respuestas Antiguas</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="py-12 px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <!-- Current User Info -->
            <div class="mb-8 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">
                            Tu Información Actual
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Verifica si tienes acceso a tus respuestas antiguas
                        </p>
                    </div>

                    @if($user)
                        <div class="space-y-6">
                            <!-- User Details Grid -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Email
                                    </p>
                                    <p class="mt-2 text-sm font-medium text-gray-900">
                                        {{ $user->email }}
                                    </p>
                                </div>
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Nombre
                                    </p>
                                    <p class="mt-2 text-sm font-medium text-gray-900">
                                        {{ $user->name }}
                                    </p>
                                </div>
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        ID Usuario
                                    </p>
                                    <p class="mt-2 text-sm font-medium text-gray-900">
                                        #{{ $user->id }}
                                    </p>
                                </div>
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-600">
                                        Roles
                                    </p>
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        @foreach($user->roles as $role)
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $role->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Egresado Status Section -->
                            <div class="border-t border-gray-200 pt-6">
                                @if($egresado)
                                    <div class="rounded-lg border border-green-200 bg-green-50 p-4">
                                        <div class="flex items-start gap-3">
                                            <svg class="h-5 w-5 flex-shrink-0 text-green-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            <div class="flex-1">
                                                <p class="font-semibold text-green-900">
                                                    ✓ Egresado Encontrado
                                                </p>
                                                <p class="mt-1 text-sm text-green-800">
                                                    {{ $egresado['nombre'] }} {{ $egresado['apellidos'] }}
                                                </p>
                                                <div class="mt-3 flex items-center gap-4">
                                                    <span class="text-sm font-semibold text-green-900">
                                                        Respuestas Antiguas: <span class="text-lg">{{ $egresado['bitacoras_count'] }}</span>
                                                    </span>
                                                    @if($egresado['bitacoras_count'] > 0)
                                                        <a
                                                            href="/respuestas-antiguas"
                                                            class="inline-flex items-center px-4 py-2 rounded-md bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition"
                                                        >
                                                            Ver Respuestas →
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4">
                                        <div class="flex items-start gap-3">
                                            <svg class="h-5 w-5 flex-shrink-0 text-yellow-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                            <div class="flex-1">
                                                <p class="font-semibold text-yellow-900">
                                                    ⚠ No hay Egresado Asociado
                                                </p>
                                                <p class="mt-1 text-sm text-yellow-800">
                                                    No se encontró un egresado con el email <strong>{{ $user->email }}</strong>
                                                </p>
                                                <p class="mt-2 text-xs text-yellow-700">
                                                    Tu usuario probablemente no existía en el sistema anterior. Prueba con uno de los emails que aparecen abajo.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Available Users List -->
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">
                            Egresados con Respuestas Antiguas
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ count($usersWithBitacoras) }} usuarios disponibles para prueba
                        </p>
                    </div>

                    <div class="space-y-2">
                        @forelse($usersWithBitacoras as $user_item)
                            <div class="flex items-center justify-between rounded-lg border border-gray-200 p-4 hover:border-blue-300 hover:bg-blue-50 transition-colors">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">
                                        {{ $user_item['nombre'] }} {{ $user_item['apellidos'] }}
                                    </p>
                                    <p class="mt-1 text-sm text-gray-600 font-mono">
                                        {{ $user_item['email'] }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                        {{ $user_item['bitacoras'] }} respuestas
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <p class="text-gray-500">No hay egresados con respuestas antiguas</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="mt-8 rounded-lg border border-blue-200 bg-blue-50 p-6">
                <p class="font-semibold text-blue-900 mb-3">
                    💡 ¿Cómo usar esta página?
                </p>
                <ul class="space-y-2 text-sm text-blue-800">
                    <li>• Si tienes respuestas antiguas, verás el número en tu card de arriba</li>
                    <li>• Si no apareces en la lista de abajo, tu usuario no estaba en el sistema anterior</li>
                    <li>• Puedes copiar cualquier email de abajo para loguearte y probar la funcionalidad</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
