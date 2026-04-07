<?php
// ══════════════════════════════════════════════════════════════════
//  ModeloDashboard.php — SEGTRACK
//  Ruta: App/Model/ModeloDashboard.php
//
//  ✅ Funciona para: Personal Seguridad, Supervisor, Administrador
//    Los métodos existentes NO se modificaron.
//      Solo se agregaron métodos nuevos al final para el Admin:
//      TotalSedes, TotalInstituciones, SedesPorCiudad,
//      SedesPorInstitucion, TodasLasSedes, InstitucionesPorTipo
// ══════════════════════════════════════════════════════════════════

class ModeloDashboard {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ── HELPERS ──────────────────────────────────────────────────

    private function query(string $sql): array {
        $stmt = $this->pdo->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    private function scalar(string $sql): int {
        $stmt = $this->pdo->query($sql);
        if (!$stmt) return 0;
        $row = $stmt->fetch(PDO::FETCH_NUM);
        return $row ? (int)$row[0] : 0;
    }

    // ════════════════════════════════════════════════════════════
    //  TOTALES — sin cambios
    // ════════════════════════════════════════════════════════════

    public function FuncionariosTotal(): int {
        return $this->scalar("SELECT COUNT(*) FROM funcionario WHERE Estado = 'Activo'");
    }

    public function TotalVisitante(): int {
        return $this->scalar("SELECT COUNT(*) FROM visitante WHERE Estado = 'Activo'");
    }

    public function ParqueaderoTotal(): int {
        return $this->scalar("SELECT COUNT(*) FROM vehiculo WHERE Estado = 'Activo'");
    }

    public function DispositivosTotal(): int {
        return $this->scalar("SELECT COUNT(*) FROM dispositivo WHERE Estado = 'Activo'");
    }

    public function DotacionTotal(): int {
        return $this->scalar("SELECT COUNT(*) FROM dotacion WHERE Estado = 'Activo'");
    }

    public function TotalBitacora(): int {
        return $this->scalar("SELECT COUNT(*) FROM bitacora");
    }

    // ════════════════════════════════════════════════════════════
    //  DISPOSITIVOS — sin cambios
    //  Columnas: tipo_dispositivos, cantidad_Dispositivos
    // ════════════════════════════════════════════════════════════

    public function DispositivosPorTipo(): array {
        return $this->query(
            "SELECT TipoDispositivo AS tipo_dispositivos,
                    COUNT(*)        AS cantidad_Dispositivos
             FROM dispositivo
             WHERE Estado = 'Activo'
             GROUP BY TipoDispositivo
             ORDER BY cantidad_Dispositivos DESC"
        );
    }

    // ════════════════════════════════════════════════════════════
    //  VEHÍCULOS — sin cambios
    //  PorTipo → tipo_vehiculos, cantidad_Vehiculos
    //  PorSede → NombreSede, total
    // ════════════════════════════════════════════════════════════

    public function VehiculosPorTipo(): array {
        return $this->query(
            "SELECT TipoVehiculo AS tipo_vehiculos,
                    COUNT(*)     AS cantidad_Vehiculos
             FROM vehiculo
             WHERE Estado = 'Activo'
             GROUP BY TipoVehiculo
             ORDER BY cantidad_Vehiculos DESC"
        );
    }

    public function VehiculosPorSede(): array {
        return $this->query(
            "SELECT CONCAT(s.TipoSede, ' - ', s.Ciudad) AS NombreSede,
                    COUNT(v.IdVehiculo)                  AS total
             FROM sede s
             LEFT JOIN vehiculo v ON v.IdSede = s.IdSede AND v.Estado = 'Activo'
             WHERE s.Estado = 'Activo'
             GROUP BY s.IdSede, s.TipoSede, s.Ciudad
             ORDER BY total DESC"
        );
    }

    // ════════════════════════════════════════════════════════════
    //  FUNCIONARIOS — sin cambios
    //  PorCargo → CargoFuncionario, total
    //  PorSede  → NombreSede, total
    // ════════════════════════════════════════════════════════════

    public function FuncionariosPorCargo(): array {
        return $this->query(
            "SELECT CargoFuncionario,
                    COUNT(*) AS total
             FROM funcionario
             WHERE Estado = 'Activo'
             GROUP BY CargoFuncionario
             ORDER BY total DESC"
        );
    }

    public function FuncionariosPorSede(): array {
        return $this->query(
            "SELECT CONCAT(s.TipoSede, ' - ', s.Ciudad) AS NombreSede,
                    COUNT(f.IdFuncionario)               AS total
             FROM sede s
             LEFT JOIN funcionario f ON f.IdSede = s.IdSede AND f.Estado = 'Activo'
             WHERE s.Estado = 'Activo'
             GROUP BY s.IdSede, s.TipoSede, s.Ciudad
             ORDER BY total DESC"
        );
    }

    // ════════════════════════════════════════════════════════════
    //  INGRESOS — sin cambios
    //  PorMes  → mes (YYYY-MM), total
    //  PorTipo → tipo, total
    // ════════════════════════════════════════════════════════════

    public function VisitantesPorMes(): array {
        return $this->query(
            "SELECT DATE_FORMAT(FechaIngreso, '%Y-%m') AS mes,
                    COUNT(*)                           AS total
             FROM ingreso
             WHERE FechaIngreso >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY mes
             ORDER BY mes ASC"
        );
    }

    public function IngresosPorTipo(): array {
        return $this->query(
            "SELECT TipoMovimiento AS tipo,
                    COUNT(*)       AS total
             FROM ingreso
             GROUP BY TipoMovimiento"
        );
    }

    // ════════════════════════════════════════════════════════════
    //  DOTACIÓN — sin cambios
    //  PorTipo       → tipo_dotaciones, cantidad_dotaciones
    //  PorEstado     → estado_dotaciones, cantidad_estado_dotaciones
    //  PorMes        → mes, cantidad
    //  PorDevolucion → mes, cantidad
    // ════════════════════════════════════════════════════════════

    public function DotacionPorTipo(): array {
        return $this->query(
            "SELECT TipoDotacion AS tipo_dotaciones,
                    COUNT(*)     AS cantidad_dotaciones
             FROM dotacion
             WHERE Estado = 'Activo'
             GROUP BY TipoDotacion
             ORDER BY cantidad_dotaciones DESC"
        );
    }

    public function DotacionPorEstado(): array {
        return $this->query(
            "SELECT EstadoDotacion AS estado_dotaciones,
                    COUNT(*)       AS cantidad_estado_dotaciones
             FROM dotacion
             WHERE Estado = 'Activo'
             GROUP BY EstadoDotacion"
        );
    }

    public function DotacionesPorMes(): array {
        return $this->query(
            "SELECT DATE_FORMAT(FechaEntrega, '%Y-%m') AS mes,
                    COUNT(*)                           AS cantidad
             FROM dotacion
             WHERE FechaEntrega >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY mes
             ORDER BY mes ASC"
        );
    }

    public function DotacionesPorDevolucionMes(): array {
        return $this->query(
            "SELECT DATE_FORMAT(FechaDevolucion, '%Y-%m') AS mes,
                    COUNT(*)                              AS cantidad
             FROM dotacion
             WHERE FechaDevolucion >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY mes
             ORDER BY mes ASC"
        );
    }

    // ════════════════════════════════════════════════════════════
    //  BITÁCORA — sin cambios
    //  PorTurno → turno_bitacoras, cantidad_bitacoras_turno
    //  PorMes   → mes, cantidad
    // ════════════════════════════════════════════════════════════

    public function BitacoraPorTurno(): array {
        return $this->query(
            "SELECT TurnoBitacora AS turno_bitacoras,
                    COUNT(*)      AS cantidad_bitacoras_turno
             FROM bitacora
             GROUP BY TurnoBitacora"
        );
    }

    public function BitacoraPorMes(): array {
        return $this->query(
            "SELECT DATE_FORMAT(FechaBitacora, '%Y-%m') AS mes,
                    COUNT(*)                            AS cantidad
             FROM bitacora
             WHERE FechaBitacora >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY mes
             ORDER BY mes ASC"
        );
    }

    // ════════════════════════════════════════════════════════════
    //  NUEVOS MÉTODOS PARA ADMINISTRADOR
    //  (agregados al final — no afectan los métodos anteriores)
    // ════════════════════════════════════════════════════════════

    // Total sedes activas
    public function TotalSedes(): int {
        return $this->scalar("SELECT COUNT(*) FROM sede WHERE Estado = 'Activo'");
    }

    // Total instituciones activas
    public function TotalInstituciones(): int {
        return $this->scalar("SELECT COUNT(*) FROM institucion WHERE EstadoInstitucion = 'Activo'");
    }

    // Sedes agrupadas por ciudad → ciudad, total
    public function SedesPorCiudad(): array {
        return $this->query(
            "SELECT Ciudad   AS ciudad,
                    COUNT(*) AS total
             FROM sede
             WHERE Estado = 'Activo'
             GROUP BY Ciudad
             ORDER BY total DESC"
        );
    }

    // Cuántas sedes tiene cada institución → institucion, total_sedes
    public function SedesPorInstitucion(): array {
        return $this->query(
            "SELECT i.NombreInstitucion AS institucion,
                    COUNT(s.IdSede)     AS total_sedes
             FROM institucion i
             LEFT JOIN sede s ON s.IdInstitucion = i.IdInstitucion AND s.Estado = 'Activo'
             WHERE i.EstadoInstitucion = 'Activo'
             GROUP BY i.IdInstitucion, i.NombreInstitucion
             ORDER BY total_sedes DESC"
        );
    }

    // Las 12 sedes individuales con sus funcionarios
    // Agrupa por IdSede para evitar colapso de "Sede Principal" x3
    // → NombreSede (TipoSede - Ciudad), Ciudad, Institucion, total_funcionarios
    public function TodasLasSedes(): array {
        return $this->query(
            "SELECT CONCAT(s.TipoSede, ' - ', s.Ciudad) AS NombreSede,
                    s.Ciudad,
                    i.NombreInstitucion                  AS Institucion,
                    COUNT(f.IdFuncionario)               AS total_funcionarios
             FROM sede s
             LEFT JOIN institucion i ON i.IdInstitucion = s.IdInstitucion
             LEFT JOIN funcionario f ON f.IdSede = s.IdSede AND f.Estado = 'Activo'
             WHERE s.Estado = 'Activo'
             GROUP BY s.IdSede, s.TipoSede, s.Ciudad, i.NombreInstitucion
             ORDER BY i.NombreInstitucion, s.TipoSede"
        );
    }

    // Instituciones agrupadas por tipo → tipo_institucion, total
    public function InstitucionesPorTipo(): array {
        return $this->query(
            "SELECT TipoInstitucion AS tipo_institucion,
                    COUNT(*)        AS total
             FROM institucion
             WHERE EstadoInstitucion = 'Activo'
             GROUP BY TipoInstitucion
             ORDER BY total DESC"
        );
    }
}