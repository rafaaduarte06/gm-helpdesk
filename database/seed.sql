-- GM HelpDesk: dados fictícios de demonstração (Escola Demo).
-- Todos os nomes, emails, salas, equipamentos e tickets são inventados.
-- Password de todos os utilizadores de demonstração: Demo2026!
SET NAMES utf8mb4;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
START TRANSACTION;

INSERT INTO `comp_estado` (`ID_ESTADO`, `ESTADO`) VALUES
(1, 'Disponível'),
(2, 'Em Uso'),
(3, 'Manutenção'),
(4, 'Sem Reparo');

INSERT INTO `caixas_estado` (`ID_ESTADO`, `ESTADO`) VALUES
(1, 'Disponível'),
(2, 'Em Uso'),
(3, 'Manutenção'),
(4, 'Sem Reparo');

INSERT INTO `coolers_estado` (`ID_ESTADO`, `ESTADO`) VALUES
(1, 'Disponível'),
(2, 'Em Uso'),
(3, 'Manutenção'),
(4, 'Sem Reparo');

INSERT INTO `discos_estado` (`ID_ESTADO`, `ESTADO`) VALUES
(1, 'Disponível'),
(2, 'Em Uso'),
(3, 'Manutenção'),
(4, 'Sem Reparo');

INSERT INTO `drives_estado` (`ID_ESTADO`, `ESTADO`) VALUES
(1, 'Disponível'),
(2, 'Em Uso'),
(3, 'Manutenção'),
(4, 'Sem Reparo');

INSERT INTO `fontes_estado` (`ID_ESTADO`, `ESTADO`) VALUES
(1, 'Disponível'),
(2, 'Em Uso'),
(3, 'Manutenção'),
(4, 'Sem Reparo');

INSERT INTO `memoriaram_estado` (`ID_ESTADO`, `ESTADO`) VALUES
(1, 'Disponível'),
(2, 'Em Uso'),
(3, 'Manutenção'),
(4, 'Sem Reparo');

INSERT INTO `monitores_estado` (`ID_ESTADO`, `ESTADO`) VALUES
(1, 'Disponível'),
(2, 'Em Uso'),
(3, 'Manutenção'),
(4, 'Sem Reparo');

INSERT INTO `placagrafica_estado` (`ID_ESTADO`, `ESTADO`) VALUES
(1, 'Disponível'),
(2, 'Em Uso'),
(3, 'Manutenção'),
(4, 'Sem Reparo');

INSERT INTO `placamae_estado` (`ID_ESTADO`, `ESTADO`) VALUES
(1, 'Disponível'),
(2, 'Em Uso'),
(3, 'Manutenção'),
(4, 'Sem Reparo');

INSERT INTO `placarede_estado` (`ID_ESTADO`, `ESTADO`) VALUES
(1, 'Disponível'),
(2, 'Em Uso'),
(3, 'Manutenção'),
(4, 'Sem Reparo');

INSERT INTO `processadores_estado` (`ID_ESTADO`, `ESTADO`) VALUES
(1, 'Disponível'),
(2, 'Em Uso'),
(3, 'Manutenção'),
(4, 'Sem Reparo');

INSERT INTO `rato_estado` (`ID_ESTADO`, `ESTADO`) VALUES
(1, 'Disponível'),
(2, 'Em Uso'),
(3, 'Manutenção'),
(4, 'Sem Reparo');

INSERT INTO `teclados_estado` (`ID_ESTADO`, `ESTADO`) VALUES
(1, 'Disponível'),
(2, 'Em Uso'),
(3, 'Manutenção'),
(4, 'Sem Reparo');

INSERT INTO `ventoinhas_estado` (`ID_ESTADO`, `ESTADO`) VALUES
(1, 'Disponível'),
(2, 'Em Uso'),
(3, 'Manutenção'),
(4, 'Sem Reparo');

INSERT INTO `marcacaixa` (`MC_ID`, `MC_NOME`) VALUES
(1, 'Cooler Master'),
(2, 'Corsair'),
(3, 'NZXT');

INSERT INTO `marcacooler` (`MCO_ID`, `MCO_NOME`) VALUES
(1, 'Cooler Master'),
(2, 'Noctua'),
(3, 'Arctic');

INSERT INTO `marcadisco` (`MDSC_ID`, `MDSC_NOME`) VALUES
(1, 'Kingston'),
(2, 'Samsung'),
(3, 'Crucial'),
(4, 'Seagate');

INSERT INTO `marcadrdvd` (`MD_ID`, `MD_NOME`) VALUES
(1, 'LG'),
(2, 'ASUS');

INSERT INTO `marcafonte` (`MF_ID`, `MF_NOME`) VALUES
(1, 'Corsair'),
(2, 'be quiet!'),
(3, 'Seasonic');

INSERT INTO `marcamonitor` (`MM_ID`, `MM_NOME`) VALUES
(1, 'Dell'),
(2, 'LG'),
(3, 'Samsung'),
(4, 'AOC');

INSERT INTO `marcamr` (`MMR_ID`, `MMR_NOME`) VALUES
(1, 'Kingston'),
(2, 'Corsair'),
(3, 'Crucial');

INSERT INTO `marcapg` (`MPG_ID`, `MPG_NOME`) VALUES
(1, 'NVIDIA'),
(2, 'AMD');

INSERT INTO `marcapm` (`MPM_ID`, `MPM_NOME`) VALUES
(1, 'ASUS'),
(2, 'Gigabyte'),
(3, 'MSI');

INSERT INTO `marcapr` (`MPR_ID`, `MPR_NOME`) VALUES
(1, 'TP-Link'),
(2, 'Intel');

INSERT INTO `marcaproc` (`MP_ID`, `MP_NOME`) VALUES
(1, 'Intel'),
(2, 'AMD');

INSERT INTO `marcarato` (`MRATO_ID`, `MRATO_NOME`) VALUES
(1, 'Logitech'),
(2, 'Microsoft'),
(3, 'HP');

INSERT INTO `marcateclado` (`MTECLADO_ID`, `MTECLADO_NOME`) VALUES
(1, 'Logitech'),
(2, 'Microsoft'),
(3, 'HP');

INSERT INTO `marcaventoinha` (`MV_ID`, `MV_NOME`) VALUES
(1, 'Arctic'),
(2, 'Noctua'),
(3, 'Cooler Master');

INSERT INTO `cor` (`ID_COR`, `COR`) VALUES
(1, 'Preto'),
(2, 'Branco'),
(3, 'Cinzento'),
(4, 'Prateado');

INSERT INTO `caixamodelo` (`CAIXA_MODELO`) VALUES
('Mid Tower'),
('Mini Tower'),
('SFF');

INSERT INTO `coopinos` (`COO_PINOS`) VALUES
('3 pinos'),
('4 pinos (PWM)');

INSERT INTO `coosocket` (`COO_SOCKET`) VALUES
('LGA1700'),
('LGA1200'),
('AM4');

INSERT INTO `cootamanho` (`COO_TAMANHO`) VALUES
('92 mm'),
('120 mm');

INSERT INTO `cootipo` (`COO_TIPO`) VALUES
('Ar'),
('Líquido (AIO)');

INSERT INTO `discointerface` (`DISCO_INTERFACE`) VALUES
('SATA III'),
('NVMe PCIe 3.0'),
('NVMe PCIe 4.0');

INSERT INTO `discomemoria` (`DISCO_MEMORIA`) VALUES
('256 GB'),
('512 GB'),
('1 TB');

INSERT INTO `discotipo` (`DISCO_TIPO`) VALUES
('SSD'),
('HDD');

INSERT INTO `fontefrequencia` (`FONTE_FREQUENCIA`) VALUES
('50 Hz'),
('60 Hz');

INSERT INTO `fontemodelo` (`FONTE_MODELO`) VALUES
('ATX 80+ Bronze'),
('ATX 80+ Gold'),
('SFX');

INSERT INTO `fontepotencia` (`FONTE_POTENCIA`) VALUES
('450W'),
('500W'),
('650W');

INSERT INTO `memoriarmodelo` (`MR_MODELO`) VALUES
('DIMM'),
('SO-DIMM');

INSERT INTO `memoriartipo` (`MR_TIPO`) VALUES
('DDR4'),
('DDR5');

INSERT INTO `memoriarvelocidade` (`MR_VELOCIDADE`) VALUES
('2666 MHz'),
('3200 MHz'),
('4800 MHz');

INSERT INTO `monitorfrequencia` (`MONITOR_FREQUENCIA`) VALUES
('60 Hz'),
('75 Hz'),
('144 Hz');

INSERT INTO `monitorpolegada` (`MONITOR_POL`) VALUES
('22"'),
('24"'),
('27"');

INSERT INTO `pgmemoria` (`PG_MEMORIA`) VALUES
('4 GB'),
('8 GB');

INSERT INTO `pgmodelo` (`PG_MODELO`) VALUES
('GTX 1650'),
('RTX 3050'),
('RX 6500 XT');

INSERT INTO `pmchipset` (`PM_CHIPSET`) VALUES
('B660'),
('H610'),
('B550');

INSERT INTO `pmformato` (`PM_FORMATO`) VALUES
('ATX'),
('Micro-ATX'),
('Mini-ITX');

INSERT INTO `pmsocket` (`PM_SOCKET`) VALUES
('LGA1700'),
('AM4');

INSERT INTO `pmtam` (`PM_TAM`) VALUES
('30.5 x 24.4 cm'),
('24.4 x 24.4 cm');

INSERT INTO `pmtmemoria` (`PM_TMEMORIA`) VALUES
('DDR4'),
('DDR5');

INSERT INTO `prlrede` (`PR_LR`) VALUES
('Gigabit Ethernet'),
('Wi-Fi 6');

INSERT INTO `procmodelo` (`PROC_MODELO`) VALUES
('Core i5-12400'),
('Core i3-12100'),
('Ryzen 5 5600G');

INSERT INTO `procsocket` (`PROC_SOCKET`) VALUES
('LGA1700'),
('AM4');

INSERT INTO `procthreads` (`PROC_THREADS`) VALUES
('8'),
('12'),
('16');

INSERT INTO `ratowir` (`RATO_WIR`) VALUES
('Com fio'),
('Sem fio');

INSERT INTO `tecladotipo` (`TECLADO_TIPO`) VALUES
('Membrana'),
('Mecânico');

INSERT INTO `ventoinhatamanho` (`VENT_TAM`) VALUES
('80 mm'),
('120 mm'),
('140 mm');

INSERT INTO `proccore` (`PROC_CORE`) VALUES
(4),
(6),
(8);

INSERT INTO `salas` (`id_sala`, `sala`) VALUES
(1, 'A1.01'),
(2, 'A1.04'),
(3, 'B2.03'),
(4, 'B2.07'),
(5, 'LAB-1'),
(6, 'LAB-2'),
(7, 'C0.12'),
(8, 'BIBLIO');

INSERT INTO `suporte_tecnico` (`id`, `nome_completo`, `email_institucional`, `departamento_tecnico`, `numero_processo`, `senha_hash`, `data_criacao`, `primeiro_login`, `foto_perfil`) VALUES
(1, 'Ana Ribeiro', 'ana.ribeiro@escola-demo.example', 'suporte', 'T-101', '$2y$10$KzYJuVtJOeVU0dzJ4e08Pe8wMqJlxCTeYbfo7atnlCQmm1ITeLvuW', '2026-09-01 09:00:00', 0, NULL),
(2, 'Tiago Mendes', 'tiago.mendes@escola-demo.example', 'manutencao', 'T-102', '$2y$10$KzYJuVtJOeVU0dzJ4e08Pe8wMqJlxCTeYbfo7atnlCQmm1ITeLvuW', '2026-09-01 09:00:00', 0, NULL),
(3, 'Marta Lopes', 'marta.lopes@escola-demo.example', 'administrador', 'T-103', '$2y$10$KzYJuVtJOeVU0dzJ4e08Pe8wMqJlxCTeYbfo7atnlCQmm1ITeLvuW', '2026-09-01 09:00:00', 0, NULL);

INSERT INTO `comunidade_escolar` (`id`, `nome_completo`, `email_institucional`, `cargo`, `numero_processo`, `senha_hash`, `data_criacao`, `primeiro_login`, `foto_perfil`) VALUES
(1, 'Rui Carvalho', 'rui.carvalho@escola-demo.example', 'professor', 'P-2041', '$2y$10$KzYJuVtJOeVU0dzJ4e08Pe8wMqJlxCTeYbfo7atnlCQmm1ITeLvuW', '2026-09-01 09:00:00', 0, NULL),
(2, 'Inês Almeida', 'ines.almeida@escola-demo.example', 'professor', 'P-2057', '$2y$10$KzYJuVtJOeVU0dzJ4e08Pe8wMqJlxCTeYbfo7atnlCQmm1ITeLvuW', '2026-09-01 09:00:00', 0, NULL),
(3, 'Duarte Sousa', 'duarte.sousa@escola-demo.example', 'aluno', 'A-31102', '$2y$10$KzYJuVtJOeVU0dzJ4e08Pe8wMqJlxCTeYbfo7atnlCQmm1ITeLvuW', '2026-09-01 09:00:00', 0, NULL),
(4, 'Beatriz Nogueira', 'beatriz.nogueira@escola-demo.example', 'funcionario', 'F-0412', '$2y$10$KzYJuVtJOeVU0dzJ4e08Pe8wMqJlxCTeYbfo7atnlCQmm1ITeLvuW', '2026-09-01 09:00:00', 0, NULL),
(5, 'Carolina Pinto', 'carolina.pinto@escola-demo.example', 'aluno', 'A-31187', '$2y$10$KzYJuVtJOeVU0dzJ4e08Pe8wMqJlxCTeYbfo7atnlCQmm1ITeLvuW', '2026-09-01 09:00:00', 0, NULL);

INSERT INTO `ratos` (`RATOS_ID`, `RATOS_REF`, `RATO_MARCA`, `RATOS_WIR`, `RATOS_COR`, `RATOS_COMP`, `RATOS_SALA`, `RATOS_ESTADO`) VALUES
(1, 'RAT-0001', 2, 'Sem fio', 1, '1', 'LAB-1', '2'),
(2, 'RAT-0002', 3, 'Com fio', 1, '2', 'LAB-1', '2'),
(3, 'RAT-0003', 1, 'Sem fio', 1, '3', 'LAB-1', '2'),
(4, 'RAT-0004', 2, 'Com fio', 2, '4', 'LAB-1', '2'),
(5, 'RAT-0005', 3, 'Sem fio', 1, '5', 'LAB-1', '2'),
(6, 'RAT-0006', 1, 'Com fio', 1, '6', 'LAB-1', '2'),
(7, 'RAT-0007', 2, 'Sem fio', 1, '7', 'LAB-1', '2'),
(8, 'RAT-0008', 3, 'Com fio', 2, '8', 'LAB-1', '2'),
(9, 'RAT-0009', 1, 'Sem fio', 1, '9', 'LAB-2', '2'),
(10, 'RAT-0010', 2, 'Com fio', 1, '10', 'LAB-2', '2'),
(11, 'RAT-0011', 3, 'Sem fio', 1, '11', 'LAB-2', '2'),
(12, 'RAT-0012', 1, 'Com fio', 2, '12', 'LAB-2', '2'),
(13, 'RAT-0013', 2, 'Sem fio', 1, '13', 'LAB-2', '2'),
(14, 'RAT-0014', 3, 'Com fio', 1, '14', 'LAB-2', '2'),
(15, 'RAT-0015', 1, 'Sem fio', 1, '15', 'LAB-2', '2'),
(16, 'RAT-0016', 2, 'Com fio', 2, '16', 'B2.03', '2'),
(17, 'RAT-0017', 3, 'Sem fio', 1, '17', 'B2.03', '2'),
(18, 'RAT-0018', 1, 'Com fio', 1, '18', 'B2.03', '2'),
(19, 'RAT-0019', 2, 'Sem fio', 1, '19', 'B2.07', '2'),
(20, 'RAT-0020', 3, 'Com fio', 2, '20', 'A1.01', '2'),
(21, 'RAT-0021', 1, 'Sem fio', 1, '21', 'A1.01', '2'),
(22, 'RAT-0022', 2, 'Com fio', 1, '22', 'A1.04', '2'),
(23, 'RAT-0023', 3, 'Sem fio', 1, '23', 'C0.12', '2'),
(24, 'RAT-0024', 1, 'Com fio', 2, '24', 'BIBLIO', '2'),
(25, 'RAT-0025', 2, 'Sem fio', 1, '', '', '1'),
(26, 'RAT-0026', 3, 'Com fio', 1, '', '', '1'),
(27, 'RAT-0027', 1, 'Sem fio', 1, '', '', '3'),
(28, 'RAT-0028', 2, 'Com fio', 2, '', '', '1'),
(29, 'RAT-0029', 3, 'Sem fio', 1, '', '', '1'),
(30, 'RAT-0030', 1, 'Com fio', 1, '', '', '1'),
(31, 'RAT-0031', 2, 'Sem fio', 1, '', '', '1'),
(32, 'RAT-0032', 3, 'Com fio', 2, '', '', '1'),
(33, 'RAT-0033', 1, 'Sem fio', 1, '', '', '1'),
(34, 'RAT-0034', 2, 'Com fio', 1, '', '', '1'),
(35, 'RAT-0035', 3, 'Sem fio', 1, '', '', '1'),
(36, 'RAT-0036', 1, 'Com fio', 2, '', '', '3'),
(37, 'RAT-0037', 2, 'Sem fio', 1, '', '', '1'),
(38, 'RAT-0038', 3, 'Com fio', 1, '', '', '1'),
(39, 'RAT-0039', 1, 'Sem fio', 1, '', '', '1'),
(40, 'RAT-0040', 2, 'Com fio', 2, '', '', '1'),
(41, 'RAT-0041', 3, 'Sem fio', 1, '', '', '1'),
(42, 'RAT-0042', 1, 'Com fio', 1, '', '', '1'),
(43, 'RAT-0043', 2, 'Sem fio', 1, '', '', '1'),
(44, 'RAT-0044', 3, 'Com fio', 2, '', '', '1'),
(45, 'RAT-0045', 1, 'Sem fio', 1, '', '', '3'),
(46, 'RAT-0046', 2, 'Com fio', 1, '', '', '1');

INSERT INTO `teclados` (`TECLADO_ID`, `TECLADO_REF`, `TECLADO_MARCA`, `TECLADO_COR`, `TECLADO_ESTADO`, `TECLADO_SALA`, `TECLADO_TIPO`, `TECLADO_COMP`) VALUES
(1, 'TEC-0001', 2, 1, '2', 'LAB-1', 'Mecânico', '1'),
(2, 'TEC-0002', 3, 1, '2', 'LAB-1', 'Membrana', '2'),
(3, 'TEC-0003', 1, 1, '2', 'LAB-1', 'Mecânico', '3'),
(4, 'TEC-0004', 2, 2, '2', 'LAB-1', 'Membrana', '4'),
(5, 'TEC-0005', 3, 1, '2', 'LAB-1', 'Mecânico', '5'),
(6, 'TEC-0006', 1, 1, '2', 'LAB-1', 'Membrana', '6'),
(7, 'TEC-0007', 2, 1, '2', 'LAB-1', 'Mecânico', '7'),
(8, 'TEC-0008', 3, 2, '2', 'LAB-1', 'Membrana', '8'),
(9, 'TEC-0009', 1, 1, '2', 'LAB-2', 'Mecânico', '9'),
(10, 'TEC-0010', 2, 1, '2', 'LAB-2', 'Membrana', '10'),
(11, 'TEC-0011', 3, 1, '2', 'LAB-2', 'Mecânico', '11'),
(12, 'TEC-0012', 1, 2, '2', 'LAB-2', 'Membrana', '12'),
(13, 'TEC-0013', 2, 1, '2', 'LAB-2', 'Mecânico', '13'),
(14, 'TEC-0014', 3, 1, '2', 'LAB-2', 'Membrana', '14'),
(15, 'TEC-0015', 1, 1, '2', 'LAB-2', 'Mecânico', '15'),
(16, 'TEC-0016', 2, 2, '2', 'B2.03', 'Membrana', '16'),
(17, 'TEC-0017', 3, 1, '2', 'B2.03', 'Mecânico', '17'),
(18, 'TEC-0018', 1, 1, '2', 'B2.03', 'Membrana', '18'),
(19, 'TEC-0019', 2, 1, '2', 'B2.07', 'Mecânico', '19'),
(20, 'TEC-0020', 3, 2, '2', 'A1.01', 'Membrana', '20'),
(21, 'TEC-0021', 1, 1, '2', 'A1.01', 'Mecânico', '21'),
(22, 'TEC-0022', 2, 1, '2', 'A1.04', 'Membrana', '22'),
(23, 'TEC-0023', 3, 1, '2', 'C0.12', 'Mecânico', '23'),
(24, 'TEC-0024', 1, 2, '2', 'BIBLIO', 'Membrana', '24'),
(25, 'TEC-0025', 2, 1, '1', '', 'Mecânico', ''),
(26, 'TEC-0026', 3, 1, '1', '', 'Membrana', ''),
(27, 'TEC-0027', 1, 1, '3', '', 'Mecânico', ''),
(28, 'TEC-0028', 2, 2, '1', '', 'Membrana', ''),
(29, 'TEC-0029', 3, 1, '1', '', 'Mecânico', ''),
(30, 'TEC-0030', 1, 1, '1', '', 'Membrana', ''),
(31, 'TEC-0031', 2, 1, '1', '', 'Mecânico', ''),
(32, 'TEC-0032', 3, 2, '1', '', 'Membrana', ''),
(33, 'TEC-0033', 1, 1, '1', '', 'Mecânico', ''),
(34, 'TEC-0034', 2, 1, '1', '', 'Membrana', ''),
(35, 'TEC-0035', 3, 1, '1', '', 'Mecânico', ''),
(36, 'TEC-0036', 1, 2, '3', '', 'Membrana', ''),
(37, 'TEC-0037', 2, 1, '1', '', 'Mecânico', ''),
(38, 'TEC-0038', 3, 1, '1', '', 'Membrana', ''),
(39, 'TEC-0039', 1, 1, '1', '', 'Mecânico', ''),
(40, 'TEC-0040', 2, 2, '1', '', 'Membrana', ''),
(41, 'TEC-0041', 3, 1, '1', '', 'Mecânico', '');

INSERT INTO `monitor` (`MONITOR_ID`, `MONITOR_REF`, `MONITOR_MARCA`, `MONITOR_POL`, `MONITOR_FREQ`, `MONITOR_COR`, `MONITOR_SALA`, `MONITOR_COMP`, `MONITOR_ESTADO`) VALUES
(1, 'MON-0001', 2, '22"', '75 Hz', 1, 'LAB-1', '1', '2'),
(2, 'MON-0002', 3, '27"', '60 Hz', 1, 'LAB-1', '2', '2'),
(3, 'MON-0003', 4, '24"', '75 Hz', 1, 'LAB-1', '3', '2'),
(4, 'MON-0004', 1, '22"', '60 Hz', 1, 'LAB-1', '4', '2'),
(5, 'MON-0005', 2, '27"', '75 Hz', 1, 'LAB-1', '5', '2'),
(6, 'MON-0006', 3, '24"', '60 Hz', 1, 'LAB-1', '6', '2'),
(7, 'MON-0007', 4, '22"', '75 Hz', 1, 'LAB-1', '7', '2'),
(8, 'MON-0008', 1, '27"', '60 Hz', 1, 'LAB-1', '8', '2'),
(9, 'MON-0009', 2, '24"', '75 Hz', 1, 'LAB-2', '9', '2'),
(10, 'MON-0010', 3, '22"', '60 Hz', 1, 'LAB-2', '10', '2'),
(11, 'MON-0011', 4, '27"', '75 Hz', 1, 'LAB-2', '11', '2'),
(12, 'MON-0012', 1, '24"', '60 Hz', 1, 'LAB-2', '12', '2'),
(13, 'MON-0013', 2, '22"', '75 Hz', 1, 'LAB-2', '13', '2'),
(14, 'MON-0014', 3, '27"', '60 Hz', 1, 'LAB-2', '14', '2'),
(15, 'MON-0015', 4, '24"', '75 Hz', 1, 'LAB-2', '15', '2'),
(16, 'MON-0016', 1, '22"', '60 Hz', 1, 'B2.03', '16', '2'),
(17, 'MON-0017', 2, '27"', '75 Hz', 1, 'B2.03', '17', '2'),
(18, 'MON-0018', 3, '24"', '60 Hz', 1, 'B2.03', '18', '2'),
(19, 'MON-0019', 4, '22"', '75 Hz', 1, 'B2.07', '19', '2'),
(20, 'MON-0020', 1, '27"', '60 Hz', 1, 'A1.01', '20', '2'),
(21, 'MON-0021', 2, '24"', '75 Hz', 1, 'A1.01', '21', '2'),
(22, 'MON-0022', 3, '22"', '60 Hz', 1, 'A1.04', '22', '2'),
(23, 'MON-0023', 4, '27"', '75 Hz', 1, 'C0.12', '23', '2'),
(24, 'MON-0024', 1, '24"', '60 Hz', 1, 'BIBLIO', '24', '2'),
(25, 'MON-0025', 2, '22"', '75 Hz', 1, '', '', '1'),
(26, 'MON-0026', 3, '27"', '60 Hz', 1, '', '', '1'),
(27, 'MON-0027', 4, '24"', '75 Hz', 1, '', '', '3'),
(28, 'MON-0028', 1, '22"', '60 Hz', 1, '', '', '1'),
(29, 'MON-0029', 2, '27"', '75 Hz', 1, '', '', '1'),
(30, 'MON-0030', 3, '24"', '60 Hz', 1, '', '', '1'),
(31, 'MON-0031', 4, '22"', '75 Hz', 1, '', '', '1'),
(32, 'MON-0032', 1, '27"', '60 Hz', 1, '', '', '1'),
(33, 'MON-0033', 2, '24"', '75 Hz', 1, '', '', '1'),
(34, 'MON-0034', 3, '22"', '60 Hz', 1, '', '', '1'),
(35, 'MON-0035', 4, '27"', '75 Hz', 1, '', '', '1'),
(36, 'MON-0036', 1, '24"', '60 Hz', 1, '', '', '3'),
(37, 'MON-0037', 2, '22"', '75 Hz', 1, '', '', '1'),
(38, 'MON-0038', 3, '27"', '60 Hz', 1, '', '', '1');

INSERT INTO `cooler` (`COO_ID`, `COO_REF`, `COO_MARCA`, `COO_TAM`, `COO_COR`, `COO_PINOS`, `COO_SOCKET`, `COO_COMP`, `COO_TIPO`, `COO_SALA`, `COO_ESTADO`) VALUES
(1, 'COO-0001', 2, '120 mm', 1, '4 pinos (PWM)', 'AM4', '1', 'Ar', 'LAB-1', '2'),
(2, 'COO-0002', 3, '120 mm', 1, '4 pinos (PWM)', 'LGA1700', '2', 'Ar', 'LAB-1', '2'),
(3, 'COO-0003', 1, '120 mm', 1, '4 pinos (PWM)', 'AM4', '3', 'Ar', 'LAB-1', '2'),
(4, 'COO-0004', 2, '120 mm', 1, '4 pinos (PWM)', 'LGA1700', '4', 'Ar', 'LAB-1', '2'),
(5, 'COO-0005', 3, '120 mm', 1, '4 pinos (PWM)', 'AM4', '5', 'Ar', 'LAB-1', '2'),
(6, 'COO-0006', 1, '120 mm', 1, '4 pinos (PWM)', 'LGA1700', '6', 'Ar', 'LAB-1', '2'),
(7, 'COO-0007', 2, '120 mm', 1, '4 pinos (PWM)', 'AM4', '7', 'Ar', 'LAB-1', '2'),
(8, 'COO-0008', 3, '120 mm', 1, '4 pinos (PWM)', 'LGA1700', '8', 'Ar', 'LAB-1', '2'),
(9, 'COO-0009', 1, '120 mm', 1, '4 pinos (PWM)', 'AM4', '9', 'Ar', 'LAB-2', '2'),
(10, 'COO-0010', 2, '120 mm', 1, '4 pinos (PWM)', 'LGA1700', '10', 'Ar', 'LAB-2', '2'),
(11, 'COO-0011', 3, '120 mm', 1, '4 pinos (PWM)', 'AM4', '11', 'Ar', 'LAB-2', '2'),
(12, 'COO-0012', 1, '120 mm', 1, '4 pinos (PWM)', 'LGA1700', '12', 'Ar', 'LAB-2', '2'),
(13, 'COO-0013', 2, '120 mm', 1, '4 pinos (PWM)', 'AM4', '13', 'Ar', 'LAB-2', '2'),
(14, 'COO-0014', 3, '120 mm', 1, '4 pinos (PWM)', 'LGA1700', '14', 'Ar', 'LAB-2', '2'),
(15, 'COO-0015', 1, '120 mm', 1, '4 pinos (PWM)', 'AM4', '15', 'Ar', 'LAB-2', '2'),
(16, 'COO-0016', 2, '120 mm', 1, '4 pinos (PWM)', 'LGA1700', '16', 'Ar', 'B2.03', '2'),
(17, 'COO-0017', 3, '120 mm', 1, '4 pinos (PWM)', 'AM4', '17', 'Ar', 'B2.03', '2'),
(18, 'COO-0018', 1, '120 mm', 1, '4 pinos (PWM)', 'LGA1700', '18', 'Ar', 'B2.03', '2'),
(19, 'COO-0019', 2, '120 mm', 1, '4 pinos (PWM)', 'AM4', '19', 'Ar', 'B2.07', '2'),
(20, 'COO-0020', 3, '120 mm', 1, '4 pinos (PWM)', 'LGA1700', '20', 'Ar', 'A1.01', '2'),
(21, 'COO-0021', 1, '120 mm', 1, '4 pinos (PWM)', 'AM4', '21', 'Ar', 'A1.01', '2'),
(22, 'COO-0022', 2, '120 mm', 1, '4 pinos (PWM)', 'LGA1700', '22', 'Ar', 'A1.04', '2'),
(23, 'COO-0023', 3, '120 mm', 1, '4 pinos (PWM)', 'AM4', '23', 'Ar', 'C0.12', '2'),
(24, 'COO-0024', 1, '120 mm', 1, '4 pinos (PWM)', 'LGA1700', '24', 'Ar', 'BIBLIO', '2'),
(25, 'COO-0025', 2, '120 mm', 1, '4 pinos (PWM)', 'AM4', '', 'Ar', '', '1'),
(26, 'COO-0026', 3, '120 mm', 1, '4 pinos (PWM)', 'LGA1700', '', 'Ar', '', '1'),
(27, 'COO-0027', 1, '120 mm', 1, '4 pinos (PWM)', 'AM4', '', 'Ar', '', '3'),
(28, 'COO-0028', 2, '120 mm', 1, '4 pinos (PWM)', 'LGA1700', '', 'Ar', '', '1'),
(29, 'COO-0029', 3, '120 mm', 1, '4 pinos (PWM)', 'AM4', '', 'Ar', '', '1');

INSERT INTO `disco` (`DISCO_ID`, `DISCO_REF`, `DISCO_MARCA`, `DISCO_MEMORIA`, `DISCO_COR`, `DISCO_TIPO`, `DISCO_COMP`, `DISCO_INTERFACE`, `DISCO_SALA`, `DISCO_ESTADO`) VALUES
(1, 'DSC-0001', 2, '256 GB', 1, 'SSD', '1', 'SATA III', 'LAB-1', '2'),
(2, 'DSC-0002', 3, '1 TB', 1, 'SSD', '2', 'NVMe PCIe 3.0', 'LAB-1', '2'),
(3, 'DSC-0003', 4, '512 GB', 1, 'SSD', '3', 'SATA III', 'LAB-1', '2'),
(4, 'DSC-0004', 1, '256 GB', 1, 'SSD', '4', 'NVMe PCIe 3.0', 'LAB-1', '2'),
(5, 'DSC-0005', 2, '1 TB', 1, 'SSD', '5', 'SATA III', 'LAB-1', '2'),
(6, 'DSC-0006', 3, '512 GB', 1, 'SSD', '6', 'NVMe PCIe 3.0', 'LAB-1', '2'),
(7, 'DSC-0007', 4, '256 GB', 1, 'SSD', '7', 'SATA III', 'LAB-1', '2'),
(8, 'DSC-0008', 1, '1 TB', 1, 'SSD', '8', 'NVMe PCIe 3.0', 'LAB-1', '2'),
(9, 'DSC-0009', 2, '512 GB', 1, 'SSD', '9', 'SATA III', 'LAB-2', '2'),
(10, 'DSC-0010', 3, '256 GB', 1, 'SSD', '10', 'NVMe PCIe 3.0', 'LAB-2', '2'),
(11, 'DSC-0011', 4, '1 TB', 1, 'SSD', '11', 'SATA III', 'LAB-2', '2'),
(12, 'DSC-0012', 1, '512 GB', 1, 'SSD', '12', 'NVMe PCIe 3.0', 'LAB-2', '2'),
(13, 'DSC-0013', 2, '256 GB', 1, 'SSD', '13', 'SATA III', 'LAB-2', '2'),
(14, 'DSC-0014', 3, '1 TB', 1, 'SSD', '14', 'NVMe PCIe 3.0', 'LAB-2', '2'),
(15, 'DSC-0015', 4, '512 GB', 1, 'SSD', '15', 'SATA III', 'LAB-2', '2'),
(16, 'DSC-0016', 1, '256 GB', 1, 'SSD', '16', 'NVMe PCIe 3.0', 'B2.03', '2'),
(17, 'DSC-0017', 2, '1 TB', 1, 'SSD', '17', 'SATA III', 'B2.03', '2'),
(18, 'DSC-0018', 3, '512 GB', 1, 'SSD', '18', 'NVMe PCIe 3.0', 'B2.03', '2'),
(19, 'DSC-0019', 4, '256 GB', 1, 'SSD', '19', 'SATA III', 'B2.07', '2'),
(20, 'DSC-0020', 1, '1 TB', 1, 'SSD', '20', 'NVMe PCIe 3.0', 'A1.01', '2'),
(21, 'DSC-0021', 2, '512 GB', 1, 'SSD', '21', 'SATA III', 'A1.01', '2'),
(22, 'DSC-0022', 3, '256 GB', 1, 'SSD', '22', 'NVMe PCIe 3.0', 'A1.04', '2'),
(23, 'DSC-0023', 4, '1 TB', 1, 'SSD', '23', 'SATA III', 'C0.12', '2'),
(24, 'DSC-0024', 1, '512 GB', 1, 'SSD', '24', 'NVMe PCIe 3.0', 'BIBLIO', '2'),
(25, 'DSC-0025', 2, '256 GB', 1, 'SSD', '', 'SATA III', '', '1'),
(26, 'DSC-0026', 3, '1 TB', 1, 'SSD', '', 'NVMe PCIe 3.0', '', '1'),
(27, 'DSC-0027', 4, '512 GB', 1, 'SSD', '', 'SATA III', '', '3'),
(28, 'DSC-0028', 1, '256 GB', 1, 'SSD', '', 'NVMe PCIe 3.0', '', '1'),
(29, 'DSC-0029', 2, '1 TB', 1, 'SSD', '', 'SATA III', '', '1'),
(30, 'DSC-0030', 3, '512 GB', 1, 'SSD', '', 'NVMe PCIe 3.0', '', '1'),
(31, 'DSC-0031', 4, '256 GB', 1, 'SSD', '', 'SATA III', '', '1'),
(32, 'DSC-0032', 1, '1 TB', 1, 'SSD', '', 'NVMe PCIe 3.0', '', '1'),
(33, 'DSC-0033', 2, '512 GB', 1, 'SSD', '', 'SATA III', '', '1');

INSERT INTO `fonte` (`FONTE_ID`, `FONTE_REF`, `FONTE_MARCA`, `FONTE_MODELO`, `FONTE_COR`, `FONTE_COMP`, `FONTE_POTENCIA`, `FONTE_FREQUENCIA`, `FONTE_SALA`, `FONTE_ESTADO`) VALUES
(1, 'FNT-0001', 2, 'ATX 80+ Gold', 1, '1', '450W', '50 Hz', 'LAB-1', '2'),
(2, 'FNT-0002', 3, 'ATX 80+ Bronze', 1, '2', '650W', '50 Hz', 'LAB-1', '2'),
(3, 'FNT-0003', 1, 'ATX 80+ Gold', 1, '3', '500W', '50 Hz', 'LAB-1', '2'),
(4, 'FNT-0004', 2, 'ATX 80+ Bronze', 1, '4', '450W', '50 Hz', 'LAB-1', '2'),
(5, 'FNT-0005', 3, 'ATX 80+ Gold', 1, '5', '650W', '50 Hz', 'LAB-1', '2'),
(6, 'FNT-0006', 1, 'ATX 80+ Bronze', 1, '6', '500W', '50 Hz', 'LAB-1', '2'),
(7, 'FNT-0007', 2, 'ATX 80+ Gold', 1, '7', '450W', '50 Hz', 'LAB-1', '2'),
(8, 'FNT-0008', 3, 'ATX 80+ Bronze', 1, '8', '650W', '50 Hz', 'LAB-1', '2'),
(9, 'FNT-0009', 1, 'ATX 80+ Gold', 1, '9', '500W', '50 Hz', 'LAB-2', '2'),
(10, 'FNT-0010', 2, 'ATX 80+ Bronze', 1, '10', '450W', '50 Hz', 'LAB-2', '2'),
(11, 'FNT-0011', 3, 'ATX 80+ Gold', 1, '11', '650W', '50 Hz', 'LAB-2', '2'),
(12, 'FNT-0012', 1, 'ATX 80+ Bronze', 1, '12', '500W', '50 Hz', 'LAB-2', '2'),
(13, 'FNT-0013', 2, 'ATX 80+ Gold', 1, '13', '450W', '50 Hz', 'LAB-2', '2'),
(14, 'FNT-0014', 3, 'ATX 80+ Bronze', 1, '14', '650W', '50 Hz', 'LAB-2', '2'),
(15, 'FNT-0015', 1, 'ATX 80+ Gold', 1, '15', '500W', '50 Hz', 'LAB-2', '2'),
(16, 'FNT-0016', 2, 'ATX 80+ Bronze', 1, '16', '450W', '50 Hz', 'B2.03', '2'),
(17, 'FNT-0017', 3, 'ATX 80+ Gold', 1, '17', '650W', '50 Hz', 'B2.03', '2'),
(18, 'FNT-0018', 1, 'ATX 80+ Bronze', 1, '18', '500W', '50 Hz', 'B2.03', '2'),
(19, 'FNT-0019', 2, 'ATX 80+ Gold', 1, '19', '450W', '50 Hz', 'B2.07', '2'),
(20, 'FNT-0020', 3, 'ATX 80+ Bronze', 1, '20', '650W', '50 Hz', 'A1.01', '2'),
(21, 'FNT-0021', 1, 'ATX 80+ Gold', 1, '21', '500W', '50 Hz', 'A1.01', '2'),
(22, 'FNT-0022', 2, 'ATX 80+ Bronze', 1, '22', '450W', '50 Hz', 'A1.04', '2'),
(23, 'FNT-0023', 3, 'ATX 80+ Gold', 1, '23', '650W', '50 Hz', 'C0.12', '2'),
(24, 'FNT-0024', 1, 'ATX 80+ Bronze', 1, '24', '500W', '50 Hz', 'BIBLIO', '2'),
(25, 'FNT-0025', 2, 'ATX 80+ Gold', 1, '', '450W', '50 Hz', '', '1'),
(26, 'FNT-0026', 3, 'ATX 80+ Bronze', 1, '', '650W', '50 Hz', '', '1'),
(27, 'FNT-0027', 1, 'ATX 80+ Gold', 1, '', '500W', '50 Hz', '', '3');

INSERT INTO `memoriaram` (`MR_ID`, `MR_REF`, `MR_MARCA`, `MR_TIPO`, `MR_MODELO`, `MR_COR`, `MR_VELOCIDADE`, `MR_COMP`, `MR_SALA`, `MR_ESTADO`) VALUES
(1, 'RAM-0001', 2, 'DDR4', 'DIMM', 1, '2666 MHz', '1', 'LAB-1', '2'),
(2, 'RAM-0002', 3, 'DDR4', 'DIMM', 1, '3200 MHz', '2', 'LAB-1', '2'),
(3, 'RAM-0003', 1, 'DDR4', 'DIMM', 1, '2666 MHz', '3', 'LAB-1', '2'),
(4, 'RAM-0004', 2, 'DDR4', 'DIMM', 1, '3200 MHz', '4', 'LAB-1', '2'),
(5, 'RAM-0005', 3, 'DDR4', 'DIMM', 1, '2666 MHz', '5', 'LAB-1', '2'),
(6, 'RAM-0006', 1, 'DDR4', 'DIMM', 1, '3200 MHz', '6', 'LAB-1', '2'),
(7, 'RAM-0007', 2, 'DDR4', 'DIMM', 1, '2666 MHz', '7', 'LAB-1', '2'),
(8, 'RAM-0008', 3, 'DDR4', 'DIMM', 1, '3200 MHz', '8', 'LAB-1', '2'),
(9, 'RAM-0009', 1, 'DDR4', 'DIMM', 1, '2666 MHz', '9', 'LAB-2', '2'),
(10, 'RAM-0010', 2, 'DDR4', 'DIMM', 1, '3200 MHz', '10', 'LAB-2', '2'),
(11, 'RAM-0011', 3, 'DDR4', 'DIMM', 1, '2666 MHz', '11', 'LAB-2', '2'),
(12, 'RAM-0012', 1, 'DDR4', 'DIMM', 1, '3200 MHz', '12', 'LAB-2', '2'),
(13, 'RAM-0013', 2, 'DDR4', 'DIMM', 1, '2666 MHz', '13', 'LAB-2', '2'),
(14, 'RAM-0014', 3, 'DDR4', 'DIMM', 1, '3200 MHz', '14', 'LAB-2', '2'),
(15, 'RAM-0015', 1, 'DDR4', 'DIMM', 1, '2666 MHz', '15', 'LAB-2', '2'),
(16, 'RAM-0016', 2, 'DDR4', 'DIMM', 1, '3200 MHz', '16', 'B2.03', '2'),
(17, 'RAM-0017', 3, 'DDR4', 'DIMM', 1, '2666 MHz', '17', 'B2.03', '2'),
(18, 'RAM-0018', 1, 'DDR4', 'DIMM', 1, '3200 MHz', '18', 'B2.03', '2'),
(19, 'RAM-0019', 2, 'DDR4', 'DIMM', 1, '2666 MHz', '19', 'B2.07', '2'),
(20, 'RAM-0020', 3, 'DDR4', 'DIMM', 1, '3200 MHz', '20', 'A1.01', '2'),
(21, 'RAM-0021', 1, 'DDR4', 'DIMM', 1, '2666 MHz', '21', 'A1.01', '2'),
(22, 'RAM-0022', 2, 'DDR4', 'DIMM', 1, '3200 MHz', '22', 'A1.04', '2'),
(23, 'RAM-0023', 3, 'DDR4', 'DIMM', 1, '2666 MHz', '23', 'C0.12', '2'),
(24, 'RAM-0024', 1, 'DDR4', 'DIMM', 1, '3200 MHz', '24', 'BIBLIO', '2'),
(25, 'RAM-0025', 2, 'DDR4', 'DIMM', 1, '2666 MHz', '', '', '1'),
(26, 'RAM-0026', 3, 'DDR4', 'DIMM', 1, '3200 MHz', '', '', '1'),
(27, 'RAM-0027', 1, 'DDR4', 'DIMM', 1, '2666 MHz', '', '', '3'),
(28, 'RAM-0028', 2, 'DDR4', 'DIMM', 1, '3200 MHz', '', '', '1'),
(29, 'RAM-0029', 3, 'DDR4', 'DIMM', 1, '2666 MHz', '', '', '1'),
(30, 'RAM-0030', 1, 'DDR4', 'DIMM', 1, '3200 MHz', '', '', '1'),
(31, 'RAM-0031', 2, 'DDR4', 'DIMM', 1, '2666 MHz', '', '', '1'),
(32, 'RAM-0032', 3, 'DDR4', 'DIMM', 1, '3200 MHz', '', '', '1'),
(33, 'RAM-0033', 1, 'DDR4', 'DIMM', 1, '2666 MHz', '', '', '1'),
(34, 'RAM-0034', 2, 'DDR4', 'DIMM', 1, '3200 MHz', '', '', '1'),
(35, 'RAM-0035', 3, 'DDR4', 'DIMM', 1, '2666 MHz', '', '', '1'),
(36, 'RAM-0036', 1, 'DDR4', 'DIMM', 1, '3200 MHz', '', '', '3'),
(37, 'RAM-0037', 2, 'DDR4', 'DIMM', 1, '2666 MHz', '', '', '1'),
(38, 'RAM-0038', 3, 'DDR4', 'DIMM', 1, '3200 MHz', '', '', '1'),
(39, 'RAM-0039', 1, 'DDR4', 'DIMM', 1, '2666 MHz', '', '', '1'),
(40, 'RAM-0040', 2, 'DDR4', 'DIMM', 1, '3200 MHz', '', '', '1'),
(41, 'RAM-0041', 3, 'DDR4', 'DIMM', 1, '2666 MHz', '', '', '1'),
(42, 'RAM-0042', 1, 'DDR4', 'DIMM', 1, '3200 MHz', '', '', '1'),
(43, 'RAM-0043', 2, 'DDR4', 'DIMM', 1, '2666 MHz', '', '', '1'),
(44, 'RAM-0044', 3, 'DDR4', 'DIMM', 1, '3200 MHz', '', '', '1'),
(45, 'RAM-0045', 1, 'DDR4', 'DIMM', 1, '2666 MHz', '', '', '3'),
(46, 'RAM-0046', 2, 'DDR4', 'DIMM', 1, '3200 MHz', '', '', '1'),
(47, 'RAM-0047', 3, 'DDR4', 'DIMM', 1, '2666 MHz', '', '', '1'),
(48, 'RAM-0048', 1, 'DDR4', 'DIMM', 1, '3200 MHz', '', '', '1'),
(49, 'RAM-0049', 2, 'DDR4', 'DIMM', 1, '2666 MHz', '', '', '1'),
(50, 'RAM-0050', 3, 'DDR4', 'DIMM', 1, '3200 MHz', '', '', '1'),
(51, 'RAM-0051', 1, 'DDR4', 'DIMM', 1, '2666 MHz', '', '', '1'),
(52, 'RAM-0052', 2, 'DDR4', 'DIMM', 1, '3200 MHz', '', '', '1');

INSERT INTO `placaderede` (`PR_ID`, `PR_REF`, `PR_MARCA`, `PG_COR`, `PR_LR`, `PR_COMP`, `PR_SALA`, `PR_ESTADO`) VALUES
(1, 'NIC-0001', 2, 1, 'Wi-Fi 6', '1', 'LAB-1', '2'),
(2, 'NIC-0002', 1, 1, 'Gigabit Ethernet', '2', 'LAB-1', '2'),
(3, 'NIC-0003', 2, 1, 'Wi-Fi 6', '3', 'LAB-1', '2'),
(4, 'NIC-0004', 1, 1, 'Gigabit Ethernet', '4', 'LAB-1', '2'),
(5, 'NIC-0005', 2, 1, 'Wi-Fi 6', '5', 'LAB-1', '2'),
(6, 'NIC-0006', 1, 1, 'Gigabit Ethernet', '6', 'LAB-1', '2'),
(7, 'NIC-0007', 2, 1, 'Wi-Fi 6', '7', 'LAB-1', '2'),
(8, 'NIC-0008', 1, 1, 'Gigabit Ethernet', '8', 'LAB-1', '2'),
(9, 'NIC-0009', 2, 1, 'Wi-Fi 6', '9', 'LAB-2', '2'),
(10, 'NIC-0010', 1, 1, 'Gigabit Ethernet', '10', 'LAB-2', '2'),
(11, 'NIC-0011', 2, 1, 'Wi-Fi 6', '11', 'LAB-2', '2'),
(12, 'NIC-0012', 1, 1, 'Gigabit Ethernet', '12', 'LAB-2', '2'),
(13, 'NIC-0013', 2, 1, 'Wi-Fi 6', '13', 'LAB-2', '2'),
(14, 'NIC-0014', 1, 1, 'Gigabit Ethernet', '14', 'LAB-2', '2'),
(15, 'NIC-0015', 2, 1, 'Wi-Fi 6', '15', 'LAB-2', '2'),
(16, 'NIC-0016', 1, 1, 'Gigabit Ethernet', '16', 'B2.03', '2'),
(17, 'NIC-0017', 2, 1, 'Wi-Fi 6', '17', 'B2.03', '2'),
(18, 'NIC-0018', 1, 1, 'Gigabit Ethernet', '18', 'B2.03', '2');

INSERT INTO `placagrafica` (`PG_ID`, `PG_REF`, `PG_MARCA`, `PG_MEMORIA`, `PG_MODELO`, `PG_COR`, `PG_COMP`, `PG_SALA`, `PG_ESTADO`) VALUES
(1, 'GPU-0001', 2, '8 GB', 'RTX 3050', 1, '1', 'LAB-1', '2'),
(2, 'GPU-0002', 1, '4 GB', 'RX 6500 XT', 1, '2', 'LAB-1', '2'),
(3, 'GPU-0003', 2, '8 GB', 'GTX 1650', 1, '3', 'LAB-1', '2'),
(4, 'GPU-0004', 1, '4 GB', 'RTX 3050', 1, '4', 'LAB-1', '2'),
(5, 'GPU-0005', 2, '8 GB', 'RX 6500 XT', 1, '5', 'LAB-1', '2'),
(6, 'GPU-0006', 1, '4 GB', 'GTX 1650', 1, '6', 'LAB-1', '2'),
(7, 'GPU-0007', 2, '8 GB', 'RTX 3050', 1, '7', 'LAB-1', '2'),
(8, 'GPU-0008', 1, '4 GB', 'RX 6500 XT', 1, '8', 'LAB-1', '2'),
(9, 'GPU-0009', 2, '8 GB', 'GTX 1650', 1, '9', 'LAB-2', '2'),
(10, 'GPU-0010', 1, '4 GB', 'RTX 3050', 1, '10', 'LAB-2', '2'),
(11, 'GPU-0011', 2, '8 GB', 'RX 6500 XT', 1, '11', 'LAB-2', '2'),
(12, 'GPU-0012', 1, '4 GB', 'GTX 1650', 1, '12', 'LAB-2', '2'),
(13, 'GPU-0013', 2, '8 GB', 'RTX 3050', 1, '13', 'LAB-2', '2'),
(14, 'GPU-0014', 1, '4 GB', 'RX 6500 XT', 1, '14', 'LAB-2', '2'),
(15, 'GPU-0015', 2, '8 GB', 'GTX 1650', 1, '15', 'LAB-2', '2'),
(16, 'GPU-0016', 1, '4 GB', 'RTX 3050', 1, '16', 'B2.03', '2'),
(17, 'GPU-0017', 2, '8 GB', 'RX 6500 XT', 1, '17', 'B2.03', '2'),
(18, 'GPU-0018', 1, '4 GB', 'GTX 1650', 1, '18', 'B2.03', '2'),
(19, 'GPU-0019', 2, '8 GB', 'RTX 3050', 1, '19', 'B2.07', '2'),
(20, 'GPU-0020', 1, '4 GB', 'RX 6500 XT', 1, '20', 'A1.01', '2'),
(21, 'GPU-0021', 2, '8 GB', 'GTX 1650', 1, '21', 'A1.01', '2');

INSERT INTO `placamae` (`PM_ID`, `PM_REF`, `PM_MARCA`, `PM_TAM`, `PM_SOCKET`, `PM_CHIPSET`, `PM_COR`, `PM_FORMATO`, `PM_TMEMORIA`, `PM_ESTADO`, `PM_COMP`, `PM_SALA`) VALUES
(1, 'PMB-0001', 2, '24.4 x 24.4 cm', 'AM4', 'B550', 1, 'Micro-ATX', 'DDR4', '2', '1', 'LAB-1'),
(2, 'PMB-0002', 3, '24.4 x 24.4 cm', 'LGA1700', 'B660', 1, 'Micro-ATX', 'DDR4', '2', '2', 'LAB-1'),
(3, 'PMB-0003', 1, '24.4 x 24.4 cm', 'AM4', 'B550', 1, 'Micro-ATX', 'DDR4', '2', '3', 'LAB-1'),
(4, 'PMB-0004', 2, '24.4 x 24.4 cm', 'LGA1700', 'B660', 1, 'Micro-ATX', 'DDR4', '2', '4', 'LAB-1'),
(5, 'PMB-0005', 3, '24.4 x 24.4 cm', 'AM4', 'B550', 1, 'Micro-ATX', 'DDR4', '2', '5', 'LAB-1'),
(6, 'PMB-0006', 1, '24.4 x 24.4 cm', 'LGA1700', 'B660', 1, 'Micro-ATX', 'DDR4', '2', '6', 'LAB-1'),
(7, 'PMB-0007', 2, '24.4 x 24.4 cm', 'AM4', 'B550', 1, 'Micro-ATX', 'DDR4', '2', '7', 'LAB-1'),
(8, 'PMB-0008', 3, '24.4 x 24.4 cm', 'LGA1700', 'B660', 1, 'Micro-ATX', 'DDR4', '2', '8', 'LAB-1'),
(9, 'PMB-0009', 1, '24.4 x 24.4 cm', 'AM4', 'B550', 1, 'Micro-ATX', 'DDR4', '2', '9', 'LAB-2'),
(10, 'PMB-0010', 2, '24.4 x 24.4 cm', 'LGA1700', 'B660', 1, 'Micro-ATX', 'DDR4', '2', '10', 'LAB-2'),
(11, 'PMB-0011', 3, '24.4 x 24.4 cm', 'AM4', 'B550', 1, 'Micro-ATX', 'DDR4', '2', '11', 'LAB-2'),
(12, 'PMB-0012', 1, '24.4 x 24.4 cm', 'LGA1700', 'B660', 1, 'Micro-ATX', 'DDR4', '2', '12', 'LAB-2'),
(13, 'PMB-0013', 2, '24.4 x 24.4 cm', 'AM4', 'B550', 1, 'Micro-ATX', 'DDR4', '2', '13', 'LAB-2'),
(14, 'PMB-0014', 3, '24.4 x 24.4 cm', 'LGA1700', 'B660', 1, 'Micro-ATX', 'DDR4', '2', '14', 'LAB-2'),
(15, 'PMB-0015', 1, '24.4 x 24.4 cm', 'AM4', 'B550', 1, 'Micro-ATX', 'DDR4', '2', '15', 'LAB-2'),
(16, 'PMB-0016', 2, '24.4 x 24.4 cm', 'LGA1700', 'B660', 1, 'Micro-ATX', 'DDR4', '2', '16', 'B2.03'),
(17, 'PMB-0017', 3, '24.4 x 24.4 cm', 'AM4', 'B550', 1, 'Micro-ATX', 'DDR4', '2', '17', 'B2.03'),
(18, 'PMB-0018', 1, '24.4 x 24.4 cm', 'LGA1700', 'B660', 1, 'Micro-ATX', 'DDR4', '2', '18', 'B2.03'),
(19, 'PMB-0019', 2, '24.4 x 24.4 cm', 'AM4', 'B550', 1, 'Micro-ATX', 'DDR4', '2', '19', 'B2.07'),
(20, 'PMB-0020', 3, '24.4 x 24.4 cm', 'LGA1700', 'B660', 1, 'Micro-ATX', 'DDR4', '2', '20', 'A1.01'),
(21, 'PMB-0021', 1, '24.4 x 24.4 cm', 'AM4', 'B550', 1, 'Micro-ATX', 'DDR4', '2', '21', 'A1.01'),
(22, 'PMB-0022', 2, '24.4 x 24.4 cm', 'LGA1700', 'B660', 1, 'Micro-ATX', 'DDR4', '2', '22', 'A1.04'),
(23, 'PMB-0023', 3, '24.4 x 24.4 cm', 'AM4', 'B550', 1, 'Micro-ATX', 'DDR4', '2', '23', 'C0.12'),
(24, 'PMB-0024', 1, '24.4 x 24.4 cm', 'LGA1700', 'B660', 1, 'Micro-ATX', 'DDR4', '2', '24', 'BIBLIO'),
(25, 'PMB-0025', 2, '24.4 x 24.4 cm', 'AM4', 'B550', 1, 'Micro-ATX', 'DDR4', '1', '', ''),
(26, 'PMB-0026', 3, '24.4 x 24.4 cm', 'LGA1700', 'B660', 1, 'Micro-ATX', 'DDR4', '1', '', '');

INSERT INTO `processador` (`PROC_ID`, `PROC_REF`, `PROC_MARCA`, `PROC_MODELO`, `PROC_CORE`, `PROC_COR`, `PROC_THREADS`, `PROC_SOCKET`, `PROC_ESTADO`, `PROC_COMP`, `PROC_SALA`) VALUES
(1, 'CPU-0001', 2, 'Ryzen 5 5600G', 6, 3, '12', 'AM4', '2', '1', 'LAB-1'),
(2, 'CPU-0002', 1, 'Core i5-12400', 6, 3, '12', 'LGA1700', '2', '2', 'LAB-1'),
(3, 'CPU-0003', 2, 'Ryzen 5 5600G', 6, 3, '12', 'AM4', '2', '3', 'LAB-1'),
(4, 'CPU-0004', 1, 'Core i5-12400', 6, 3, '12', 'LGA1700', '2', '4', 'LAB-1'),
(5, 'CPU-0005', 2, 'Ryzen 5 5600G', 6, 3, '12', 'AM4', '2', '5', 'LAB-1'),
(6, 'CPU-0006', 1, 'Core i5-12400', 6, 3, '12', 'LGA1700', '2', '6', 'LAB-1'),
(7, 'CPU-0007', 2, 'Ryzen 5 5600G', 6, 3, '12', 'AM4', '2', '7', 'LAB-1'),
(8, 'CPU-0008', 1, 'Core i5-12400', 6, 3, '12', 'LGA1700', '2', '8', 'LAB-1'),
(9, 'CPU-0009', 2, 'Ryzen 5 5600G', 6, 3, '12', 'AM4', '2', '9', 'LAB-2'),
(10, 'CPU-0010', 1, 'Core i5-12400', 6, 3, '12', 'LGA1700', '2', '10', 'LAB-2'),
(11, 'CPU-0011', 2, 'Ryzen 5 5600G', 6, 3, '12', 'AM4', '2', '11', 'LAB-2'),
(12, 'CPU-0012', 1, 'Core i5-12400', 6, 3, '12', 'LGA1700', '2', '12', 'LAB-2'),
(13, 'CPU-0013', 2, 'Ryzen 5 5600G', 6, 3, '12', 'AM4', '2', '13', 'LAB-2'),
(14, 'CPU-0014', 1, 'Core i5-12400', 6, 3, '12', 'LGA1700', '2', '14', 'LAB-2'),
(15, 'CPU-0015', 2, 'Ryzen 5 5600G', 6, 3, '12', 'AM4', '2', '15', 'LAB-2'),
(16, 'CPU-0016', 1, 'Core i5-12400', 6, 3, '12', 'LGA1700', '2', '16', 'B2.03'),
(17, 'CPU-0017', 2, 'Ryzen 5 5600G', 6, 3, '12', 'AM4', '2', '17', 'B2.03'),
(18, 'CPU-0018', 1, 'Core i5-12400', 6, 3, '12', 'LGA1700', '2', '18', 'B2.03'),
(19, 'CPU-0019', 2, 'Ryzen 5 5600G', 6, 3, '12', 'AM4', '2', '19', 'B2.07'),
(20, 'CPU-0020', 1, 'Core i5-12400', 6, 3, '12', 'LGA1700', '2', '20', 'A1.01'),
(21, 'CPU-0021', 2, 'Ryzen 5 5600G', 6, 3, '12', 'AM4', '2', '21', 'A1.01'),
(22, 'CPU-0022', 1, 'Core i5-12400', 6, 3, '12', 'LGA1700', '2', '22', 'A1.04'),
(23, 'CPU-0023', 2, 'Ryzen 5 5600G', 6, 3, '12', 'AM4', '2', '23', 'C0.12'),
(24, 'CPU-0024', 1, 'Core i5-12400', 6, 3, '12', 'LGA1700', '2', '24', 'BIBLIO'),
(25, 'CPU-0025', 2, 'Ryzen 5 5600G', 6, 3, '12', 'AM4', '1', '', ''),
(26, 'CPU-0026', 1, 'Core i5-12400', 6, 3, '12', 'LGA1700', '1', '', '');

INSERT INTO `caixa` (`CAIXA_ID`, `CAIXA_REF`, `CAIXA_MARCA`, `CAIXA_MODELO`, `CAIXA_COR`, `CAIXA_COMP`, `CAIXA_SALA`, `CAIXA_ESTADO`) VALUES
(1, 'CXA-0001', 2, 'Mini Tower', 1, '1', 'LAB-1', '2'),
(2, 'CXA-0002', 3, 'Mid Tower', 1, '2', 'LAB-1', '2'),
(3, 'CXA-0003', 1, 'Mini Tower', 1, '3', 'LAB-1', '2'),
(4, 'CXA-0004', 2, 'Mid Tower', 2, '4', 'LAB-1', '2'),
(5, 'CXA-0005', 3, 'Mini Tower', 1, '5', 'LAB-1', '2'),
(6, 'CXA-0006', 1, 'Mid Tower', 1, '6', 'LAB-1', '2'),
(7, 'CXA-0007', 2, 'Mini Tower', 1, '7', 'LAB-1', '2'),
(8, 'CXA-0008', 3, 'Mid Tower', 2, '8', 'LAB-1', '2'),
(9, 'CXA-0009', 1, 'Mini Tower', 1, '9', 'LAB-2', '2'),
(10, 'CXA-0010', 2, 'Mid Tower', 1, '10', 'LAB-2', '2'),
(11, 'CXA-0011', 3, 'Mini Tower', 1, '11', 'LAB-2', '2'),
(12, 'CXA-0012', 1, 'Mid Tower', 2, '12', 'LAB-2', '2'),
(13, 'CXA-0013', 2, 'Mini Tower', 1, '13', 'LAB-2', '2'),
(14, 'CXA-0014', 3, 'Mid Tower', 1, '14', 'LAB-2', '2'),
(15, 'CXA-0015', 1, 'Mini Tower', 1, '15', 'LAB-2', '2'),
(16, 'CXA-0016', 2, 'Mid Tower', 2, '16', 'B2.03', '2'),
(17, 'CXA-0017', 3, 'Mini Tower', 1, '17', 'B2.03', '2'),
(18, 'CXA-0018', 1, 'Mid Tower', 1, '18', 'B2.03', '2'),
(19, 'CXA-0019', 2, 'Mini Tower', 1, '19', 'B2.07', '2'),
(20, 'CXA-0020', 3, 'Mid Tower', 2, '20', 'A1.01', '2'),
(21, 'CXA-0021', 1, 'Mini Tower', 1, '21', 'A1.01', '2'),
(22, 'CXA-0022', 2, 'Mid Tower', 1, '22', 'A1.04', '2'),
(23, 'CXA-0023', 3, 'Mini Tower', 1, '23', 'C0.12', '2'),
(24, 'CXA-0024', 1, 'Mid Tower', 2, '24', 'BIBLIO', '2'),
(25, 'CXA-0025', 2, 'Mini Tower', 1, '', '', '1');

INSERT INTO `drivedvd` (`DRVD_ID`, `DRVD_REF`, `DRVD_MARCA`, `DRVD_COR`, `DRVD_COMP`, `DRVD_SALA`, `DRVD_ESTADO`) VALUES
(1, 'DVD-0001', 2, 1, '1', 'LAB-1', '2'),
(2, 'DVD-0002', 1, 1, '2', 'LAB-1', '2'),
(3, 'DVD-0003', 2, 1, '3', 'LAB-1', '2'),
(4, 'DVD-0004', 1, 1, '4', 'LAB-1', '2'),
(5, 'DVD-0005', 2, 1, '5', 'LAB-1', '2'),
(6, 'DVD-0006', 1, 1, '6', 'LAB-1', '2'),
(7, 'DVD-0007', 2, 1, '7', 'LAB-1', '2'),
(8, 'DVD-0008', 1, 1, '8', 'LAB-1', '2'),
(9, 'DVD-0009', 2, 1, '9', 'LAB-2', '2'),
(10, 'DVD-0010', 1, 1, '10', 'LAB-2', '2'),
(11, 'DVD-0011', 2, 1, '11', 'LAB-2', '2'),
(12, 'DVD-0012', 1, 1, '12', 'LAB-2', '2'),
(13, 'DVD-0013', 2, 1, '13', 'LAB-2', '2'),
(14, 'DVD-0014', 1, 1, '14', 'LAB-2', '2'),
(15, 'DVD-0015', 2, 1, '15', 'LAB-2', '2'),
(16, 'DVD-0016', 1, 1, '16', 'B2.03', '2'),
(17, 'DVD-0017', 2, 1, '17', 'B2.03', '2'),
(18, 'DVD-0018', 1, 1, '18', 'B2.03', '2'),
(19, 'DVD-0019', 2, 1, '19', 'B2.07', '2');

INSERT INTO `ventoinha` (`VENT_ID`, `VENT_REF`, `VENT_MARCA`, `VENT_TAM`, `VENT_ESTADO`, `VENT_COR`, `VENT_COMP`, `VENT_SALA`) VALUES
(1, 'VNT-0001', 2, '80 mm', '2', 1, '1', 'LAB-1'),
(2, 'VNT-0002', 3, '140 mm', '2', 1, '2', 'LAB-1'),
(3, 'VNT-0003', 1, '120 mm', '2', 1, '3', 'LAB-1'),
(4, 'VNT-0004', 2, '80 mm', '2', 1, '4', 'LAB-1'),
(5, 'VNT-0005', 3, '140 mm', '2', 1, '5', 'LAB-1'),
(6, 'VNT-0006', 1, '120 mm', '2', 1, '6', 'LAB-1'),
(7, 'VNT-0007', 2, '80 mm', '2', 1, '7', 'LAB-1'),
(8, 'VNT-0008', 3, '140 mm', '2', 1, '8', 'LAB-1'),
(9, 'VNT-0009', 1, '120 mm', '2', 1, '9', 'LAB-2'),
(10, 'VNT-0010', 2, '80 mm', '2', 1, '10', 'LAB-2'),
(11, 'VNT-0011', 3, '140 mm', '2', 1, '11', 'LAB-2'),
(12, 'VNT-0012', 1, '120 mm', '2', 1, '12', 'LAB-2'),
(13, 'VNT-0013', 2, '80 mm', '2', 1, '13', 'LAB-2'),
(14, 'VNT-0014', 3, '140 mm', '2', 1, '14', 'LAB-2'),
(15, 'VNT-0015', 1, '120 mm', '2', 1, '15', 'LAB-2'),
(16, 'VNT-0016', 2, '80 mm', '2', 1, '16', 'B2.03'),
(17, 'VNT-0017', 3, '140 mm', '2', 1, '17', 'B2.03'),
(18, 'VNT-0018', 1, '120 mm', '2', 1, '18', 'B2.03'),
(19, 'VNT-0019', 2, '80 mm', '2', 1, '19', 'B2.07'),
(20, 'VNT-0020', 3, '140 mm', '2', 1, '20', 'A1.01'),
(21, 'VNT-0021', 1, '120 mm', '2', 1, '21', 'A1.01'),
(22, 'VNT-0022', 2, '80 mm', '2', 1, '22', 'A1.04'),
(23, 'VNT-0023', 3, '140 mm', '2', 1, '23', 'C0.12'),
(24, 'VNT-0024', 1, '120 mm', '2', 1, '24', 'BIBLIO'),
(25, 'VNT-0025', 2, '80 mm', '1', 1, '', ''),
(26, 'VNT-0026', 3, '140 mm', '1', 1, '', ''),
(27, 'VNT-0027', 1, '120 mm', '3', 1, '', ''),
(28, 'VNT-0028', 2, '80 mm', '1', 1, '', ''),
(29, 'VNT-0029', 3, '140 mm', '1', 1, '', ''),
(30, 'VNT-0030', 1, '120 mm', '1', 1, '', ''),
(31, 'VNT-0031', 2, '80 mm', '1', 1, '', ''),
(32, 'VNT-0032', 3, '140 mm', '1', 1, '', ''),
(33, 'VNT-0033', 1, '120 mm', '1', 1, '', ''),
(34, 'VNT-0034', 2, '80 mm', '1', 1, '', ''),
(35, 'VNT-0035', 3, '140 mm', '1', 1, '', ''),
(36, 'VNT-0036', 1, '120 mm', '3', 1, '', ''),
(37, 'VNT-0037', 2, '80 mm', '1', 1, '', ''),
(38, 'VNT-0038', 3, '140 mm', '1', 1, '', ''),
(39, 'VNT-0039', 1, '120 mm', '1', 1, '', ''),
(40, 'VNT-0040', 2, '80 mm', '1', 1, '', ''),
(41, 'VNT-0041', 3, '140 mm', '1', 1, '', ''),
(42, 'VNT-0042', 1, '120 mm', '1', 1, '', ''),
(43, 'VNT-0043', 2, '80 mm', '1', 1, '', ''),
(44, 'VNT-0044', 3, '140 mm', '1', 1, '', ''),
(45, 'VNT-0045', 1, '120 mm', '3', 1, '', ''),
(46, 'VNT-0046', 2, '80 mm', '1', 1, '', ''),
(47, 'VNT-0047', 3, '140 mm', '1', 1, '', ''),
(48, 'VNT-0048', 1, '120 mm', '1', 1, '', ''),
(49, 'VNT-0049', 2, '80 mm', '1', 1, '', ''),
(50, 'VNT-0050', 3, '140 mm', '1', 1, '', ''),
(51, 'VNT-0051', 1, '120 mm', '1', 1, '', ''),
(52, 'VNT-0052', 2, '80 mm', '1', 1, '', ''),
(53, 'VNT-0053', 3, '140 mm', '1', 1, '', ''),
(54, 'VNT-0054', 1, '120 mm', '3', 1, '', ''),
(55, 'VNT-0055', 2, '80 mm', '1', 1, '', ''),
(56, 'VNT-0056', 3, '140 mm', '1', 1, '', ''),
(57, 'VNT-0057', 1, '120 mm', '1', 1, '', ''),
(58, 'VNT-0058', 2, '80 mm', '1', 1, '', ''),
(59, 'VNT-0059', 3, '140 mm', '1', 1, '', ''),
(60, 'VNT-0060', 1, '120 mm', '1', 1, '', ''),
(61, 'VNT-0061', 2, '80 mm', '1', 1, '', ''),
(62, 'VNT-0062', 3, '140 mm', '1', 1, '', ''),
(63, 'VNT-0063', 1, '120 mm', '3', 1, '', ''),
(64, 'VNT-0064', 2, '80 mm', '1', 1, '', '');

INSERT INTO `computadores` (`COMP_ID`, `COMP_REF`, `COMP_RATO`, `COMP_TECLADO`, `COMP_MONITOR`, `COMP_PM`, `COMP_PROC`, `COMP_COOLER`, `COMP_PG`, `COMP_DISCO`, `COMP_FONTE`, `COMP_MEMORIAR`, `COMP_DRIVEDVD`, `COMP_PR`, `COMP_VENTOINHA`, `COMP_CAIXA`, `COMP_SALA`, `COMP_ESTADO`) VALUES
('1', 'PC-LAB1-01', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', 'LAB-1', '2'),
('2', 'PC-LAB1-02', '2', '2', '2', '2', '2', '2', '2', '2', '2', '2', '2', '2', '2', '2', 'LAB-1', '2'),
('3', 'PC-LAB1-03', '3', '3', '3', '3', '3', '3', '3', '3', '3', '3', '3', '3', '3', '3', 'LAB-1', '2'),
('4', 'PC-LAB1-04', '4', '4', '4', '4', '4', '4', '4', '4', '4', '4', '4', '4', '4', '4', 'LAB-1', '2'),
('5', 'PC-LAB1-05', '5', '5', '5', '5', '5', '5', '5', '5', '5', '5', '5', '5', '5', '5', 'LAB-1', '2'),
('6', 'PC-LAB1-06', '6', '6', '6', '6', '6', '6', '6', '6', '6', '6', '6', '6', '6', '6', 'LAB-1', '2'),
('7', 'PC-LAB1-07', '7', '7', '7', '7', '7', '7', '7', '7', '7', '7', '7', '7', '7', '7', 'LAB-1', '2'),
('8', 'PC-LAB1-08', '8', '8', '8', '8', '8', '8', '8', '8', '8', '8', '8', '8', '8', '8', 'LAB-1', '2'),
('9', 'PC-LAB2-01', '9', '9', '9', '9', '9', '9', '9', '9', '9', '9', '9', '9', '9', '9', 'LAB-2', '2'),
('10', 'PC-LAB2-02', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', '10', 'LAB-2', '2'),
('11', 'PC-LAB2-03', '11', '11', '11', '11', '11', '11', '11', '11', '11', '11', '11', '11', '11', '11', 'LAB-2', '2'),
('12', 'PC-LAB2-04', '12', '12', '12', '12', '12', '12', '12', '12', '12', '12', '12', '12', '12', '12', 'LAB-2', '2'),
('13', 'PC-LAB2-05', '13', '13', '13', '13', '13', '13', '13', '13', '13', '13', '13', '13', '13', '13', 'LAB-2', '2'),
('14', 'PC-LAB2-06', '14', '14', '14', '14', '14', '14', '14', '14', '14', '14', '14', '14', '14', '14', 'LAB-2', '1'),
('15', 'PC-LAB2-07', '15', '15', '15', '15', '15', '15', '15', '15', '15', '15', '15', '15', '15', '15', 'LAB-2', '2'),
('16', 'PC-B203-01', '16', '16', '16', '16', '16', '16', '16', '16', '16', '16', '16', '16', '16', '16', 'B2.03', '2'),
('17', 'PC-B203-02', '17', '17', '17', '17', '17', '17', '17', '17', '17', '17', '17', '17', '17', '17', 'B2.03', '2'),
('18', 'PC-B203-03', '18', '18', '18', '18', '18', '18', '18', '18', '18', '18', '18', '18', '18', '18', 'B2.03', '2'),
('19', 'PC-B207-01', '19', '19', '19', '19', '19', '19', '19', '19', '19', '19', '19', '', '19', '19', 'B2.07', '2'),
('20', 'PC-A101-01', '20', '20', '20', '20', '20', '20', '20', '20', '20', '20', '', '', '20', '20', 'A1.01', '2'),
('21', 'PC-A101-02', '21', '21', '21', '21', '21', '21', '21', '21', '21', '21', '', '', '21', '21', 'A1.01', '2'),
('22', 'PC-A104-01', '22', '22', '22', '22', '22', '22', '', '22', '22', '22', '', '', '22', '22', 'A1.04', '2'),
('23', 'PC-C012-01', '23', '23', '23', '23', '23', '23', '', '23', '23', '23', '', '', '23', '23', 'C0.12', '2'),
('24', 'PC-BIB-01', '24', '24', '24', '24', '24', '24', '', '24', '24', '24', '', '', '24', '24', 'BIBLIO', '2');

INSERT INTO `tickets` (`id`, `assunto`, `sala`, `entrada`, `solicitante`, `prioridade`, `status`, `descricao`, `mensagem_erro`, `observacoes_adicionais`, `id_usuario`, `id_tecnico`, `criado_em`, `ultima_atualizacao`) VALUES
(1201, 'Ecrã azul ao iniciar o Windows', '5', '09:14:00', 'Rui Carvalho', '1', 'em_andamento', 'O computador mostra um ecrã azul (BSOD) logo após o logótipo do Windows e reinicia.', 'CRITICAL_PROCESS_DIED', 'Acontece em todas as aulas da manhã.', 1, 1, '2026-09-28 09:14:00', '2026-09-28 10:49:00'),
(1202, 'Monitor a piscar', '7', '10:32:00', 'Inês Almeida', '2', 'em_andamento', 'O monitor pisca de forma intermitente durante a utilização.', '', 'Já foi trocado o cabo de alimentação.', 2, 2, '2026-09-29 10:32:00', '2026-09-29 12:07:00'),
(1203, 'Sem ligação à rede', '3', '08:51:00', 'Rui Carvalho', '1', 'aberto', 'O computador não obtém endereço de rede e não acede à internet.', 'Rede não identificada', '', 1, NULL, '2026-09-30 08:51:00', '2026-09-30 08:51:00'),
(1204, 'Rato não responde', '8', '11:05:00', 'Beatriz Nogueira', '3', 'aberto', 'O cursor não se move; o rato parece não ter alimentação.', '', 'Rato com fio.', 4, NULL, '2026-10-01 11:05:00', '2026-10-01 11:05:00'),
(1205, 'Teclado com teclas presas', '2', '14:22:00', 'Duarte Sousa', '2', 'resolvido', 'Várias teclas ficam presas ao escrever.', '', '', 3, 3, '2026-10-02 14:22:00', '2026-10-02 15:57:00'),
(1206, 'Disco quase cheio', '1', '09:40:00', 'Inês Almeida', '2', 'em_andamento', 'O disco tem menos de 5% de espaço livre e o sistema está lento.', 'Espaço em disco insuficiente', '', 2, 1, '2026-10-03 09:40:00', '2026-10-03 11:15:00'),
(1207, 'PC muito lento ao arrancar', '6', '16:20:00', 'Rui Carvalho', '1', 'resolvido', 'O computador demora vários minutos a arrancar e bloqueia ao abrir programas.', 'S.M.A.R.T.: setores realocados', 'Ocorre desde a semana passada.', 1, 1, '2026-10-05 16:20:00', '2026-10-07 15:42:00'),
(1208, 'Ventoinha muito ruidosa', '6', '10:12:00', 'Carolina Pinto', '3', 'aberto', 'A ventoinha da caixa faz muito ruído durante as aulas.', '', '', 5, NULL, '2026-10-06 10:12:00', '2026-10-06 10:12:00'),
(1209, 'Fonte faz estalos ao ligar', '4', '13:48:00', 'Beatriz Nogueira', '3', 'em_andamento', 'Ouvem-se estalos na fonte de alimentação ao ligar o computador.', '', 'Desligado por precaução.', 4, 2, '2026-10-06 13:48:00', '2026-10-06 15:23:00'),
(1210, 'PC não arranca', '5', '08:37:00', 'Duarte Sousa', '1', 'aberto', 'O computador não liga; não há luzes nem som ao carregar no botão.', '', '', 3, NULL, '2026-10-07 08:37:00', '2026-10-07 08:37:00'),
(1211, 'Temperatura elevada no processador', '6', '11:26:00', 'Rui Carvalho', '2', 'fechado', 'O processador atinge temperaturas elevadas e o PC desliga-se.', 'CPU over temperature', '', 1, 2, '2026-10-07 11:26:00', '2026-10-07 13:01:00'),
(1212, 'Rato com duplo clique', '5', '15:03:00', 'Carolina Pinto', '3', 'resolvido', 'O botão esquerdo faz duplo clique sozinho.', '', '', 5, 1, '2026-10-07 15:03:00', '2026-10-07 16:38:00'),
(1213, 'Cabo de vídeo danificado', '1', '08:45:00', 'Inês Almeida', '2', 'aberto', 'O cabo de vídeo do monitor está danificado junto à ficha.', '', 'Imagem com interferências.', 2, NULL, '2026-10-08 08:45:00', '2026-10-08 08:45:00'),
(1214, 'Painel frontal da caixa solto', '8', '09:20:00', 'Beatriz Nogueira', '3', 'fechado', 'O painel frontal da caixa está solto.', '', '', 4, 3, '2026-10-08 09:20:00', '2026-10-08 10:55:00');

INSERT INTO `ticket_componentes` (`id`, `ticket_id`, `equipamento`, `ref_equipamento`) VALUES
(1, 1201, 'computador', '5'),
(2, 1202, 'monitor', '23'),
(3, 1203, 'computador', '17'),
(4, 1204, 'rato', '24'),
(5, 1205, 'teclado', '22'),
(6, 1206, 'disco', '20'),
(7, 1207, 'computador', '11'),
(8, 1208, 'ventoinha', '13'),
(9, 1209, 'fonte', '19'),
(10, 1210, 'computador', '2'),
(11, 1211, 'cooler', '9'),
(12, 1212, 'rato', '7'),
(13, 1213, 'monitor', '21'),
(14, 1214, 'caixa', '24');

INSERT INTO `ticket_historico` (`id`, `ticket_id`, `tecnico_id`, `acao`, `descricao`, `status_anterior`, `status_novo`, `data_acao`) VALUES
(1, 1207, 1, 'status_alterado', 'Status alterado de ''aberto'' para ''em_andamento''', 'aberto', 'em_andamento', '2026-10-06 09:12:00'),
(2, 1207, 1, 'componente_manutencao', 'Componente marcado como ''Em Manutenção''', NULL, NULL, '2026-10-06 10:05:00'),
(3, 1207, 1, 'manutencao_agendada', 'Agendada manutenção para 07/10/2026 14:00: Substituição do disco do PC-LAB2-03', NULL, NULL, '2026-10-06 11:30:00'),
(4, 1207, 1, 'componente_em_uso', 'Componente marcado como ''Em Uso'' e retornado para a sala LAB-2', NULL, NULL, '2026-10-07 15:20:00'),
(5, 1207, 1, 'status_alterado', 'SOLUÇÃO: Disco substituído por um SSD novo e sistema reposto a partir da imagem padrão.', 'em_andamento', 'resolvido', '2026-10-07 15:41:00'),
(6, 1207, 1, 'ticket_resolvido', 'Ticket resolvido. Solução: Disco substituído por um SSD novo e sistema reposto a partir da imagem padrão.', NULL, NULL, '2026-10-07 15:42:00');

INSERT INTO `ticket_comentarios` (`id`, `ticket_id`, `tecnico_id`, `comentario`, `data_comentario`) VALUES
(1, 1207, 1, 'Componente posto em manutenção', '2026-10-06 10:05:00'),
(2, 1207, 1, 'SOLUÇÃO: Disco substituído por um SSD novo e sistema reposto a partir da imagem padrão.', '2026-10-07 15:42:00');

COMMIT;
