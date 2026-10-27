-- GM HelpDesk: estrutura da base de dados (só estrutura, sem dados).
-- Importar primeiro este ficheiro e depois database/seed.sql (dados fictícios).

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET NAMES utf8mb4 */;

--
-- Estrutura da tabela `caixa`
--

CREATE TABLE `caixa` (
  `CAIXA_ID` int(11) NOT NULL,
  `CAIXA_REF` varchar(100) NOT NULL,
  `CAIXA_MARCA` varchar(100) NOT NULL,
  `CAIXA_MODELO` varchar(100) NOT NULL,
  `CAIXA_COR` varchar(100) NOT NULL,
  `CAIXA_COMP` varchar(100) NOT NULL DEFAULT '',
  `CAIXA_SALA` varchar(100) NOT NULL DEFAULT '',
  `CAIXA_ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `caixamodelo`
--

CREATE TABLE `caixamodelo` (
  `CAIXA_MODELO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `caixas_estado`
--

CREATE TABLE `caixas_estado` (
  `ID_ESTADO` int(11) NOT NULL,
  `ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `comentarios`
--

CREATE TABLE `comentarios` (
  `id` int(11) NOT NULL,
  `id_ticket` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `data_criacao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `computadores`
--

CREATE TABLE `computadores` (
  `COMP_ID` varchar(100) NOT NULL,
  `COMP_REF` varchar(100) NOT NULL,
  `COMP_RATO` varchar(100) NOT NULL,
  `COMP_TECLADO` varchar(100) NOT NULL,
  `COMP_MONITOR` varchar(100) NOT NULL,
  `COMP_PM` varchar(100) NOT NULL,
  `COMP_PROC` varchar(100) NOT NULL,
  `COMP_COOLER` varchar(100) NOT NULL,
  `COMP_PG` varchar(100) NOT NULL,
  `COMP_DISCO` varchar(100) NOT NULL,
  `COMP_FONTE` varchar(100) NOT NULL,
  `COMP_MEMORIAR` varchar(100) NOT NULL,
  `COMP_DRIVEDVD` varchar(100) NOT NULL,
  `COMP_PR` varchar(100) NOT NULL,
  `COMP_VENTOINHA` varchar(100) NOT NULL,
  `COMP_CAIXA` varchar(100) NOT NULL,
  `COMP_SALA` varchar(100) NOT NULL,
  `COMP_ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `comp_estado`
--

CREATE TABLE `comp_estado` (
  `ID_ESTADO` int(11) NOT NULL,
  `ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `comunidade_escolar`
--

CREATE TABLE `comunidade_escolar` (
  `id` int(11) NOT NULL,
  `nome_completo` varchar(255) NOT NULL,
  `email_institucional` varchar(255) NOT NULL,
  `cargo` enum('professor','aluno','funcionario') NOT NULL,
  `numero_processo` varchar(50) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `primeiro_login` tinyint(1) DEFAULT 1,
  `foto_perfil` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `cooler`
--

CREATE TABLE `cooler` (
  `COO_ID` int(11) NOT NULL,
  `COO_REF` varchar(100) NOT NULL,
  `COO_MARCA` varchar(100) NOT NULL,
  `COO_TAM` varchar(100) NOT NULL,
  `COO_COR` varchar(100) NOT NULL,
  `COO_PINOS` varchar(100) NOT NULL,
  `COO_SOCKET` varchar(100) NOT NULL,
  `COO_COMP` varchar(100) NOT NULL DEFAULT '',
  `COO_TIPO` varchar(100) NOT NULL,
  `COO_SALA` varchar(100) NOT NULL DEFAULT '',
  `COO_ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `coolers_estado`
--

CREATE TABLE `coolers_estado` (
  `ID_ESTADO` int(11) NOT NULL,
  `ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `coopinos`
--

CREATE TABLE `coopinos` (
  `COO_PINOS` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `coosocket`
--

CREATE TABLE `coosocket` (
  `COO_SOCKET` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `cootamanho`
--

CREATE TABLE `cootamanho` (
  `COO_TAMANHO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `cootipo`
--

CREATE TABLE `cootipo` (
  `COO_TIPO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `cor`
--

CREATE TABLE `cor` (
  `ID_COR` int(11) NOT NULL,
  `COR` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `cursos`
--

CREATE TABLE `cursos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `disciplinas`
--

CREATE TABLE `disciplinas` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `disco`
--

CREATE TABLE `disco` (
  `DISCO_ID` int(11) NOT NULL,
  `DISCO_REF` varchar(100) NOT NULL,
  `DISCO_MARCA` varchar(100) NOT NULL,
  `DISCO_MEMORIA` varchar(100) NOT NULL,
  `DISCO_COR` varchar(100) NOT NULL,
  `DISCO_TIPO` varchar(100) NOT NULL,
  `DISCO_COMP` varchar(100) NOT NULL DEFAULT '',
  `DISCO_INTERFACE` varchar(100) NOT NULL,
  `DISCO_SALA` varchar(100) NOT NULL DEFAULT '',
  `DISCO_ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `discointerface`
--

CREATE TABLE `discointerface` (
  `DISCO_INTERFACE` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `discomemoria`
--

CREATE TABLE `discomemoria` (
  `DISCO_MEMORIA` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `discos_estado`
--

CREATE TABLE `discos_estado` (
  `ID_ESTADO` int(11) NOT NULL,
  `ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `discotipo`
--

CREATE TABLE `discotipo` (
  `DISCO_TIPO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `drivedvd`
--

CREATE TABLE `drivedvd` (
  `DRVD_ID` int(11) NOT NULL,
  `DRVD_REF` varchar(100) NOT NULL,
  `DRVD_MARCA` varchar(100) NOT NULL,
  `DRVD_COR` varchar(100) NOT NULL,
  `DRVD_COMP` varchar(100) NOT NULL DEFAULT '',
  `DRVD_SALA` varchar(100) NOT NULL DEFAULT '',
  `DRVD_ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `drives_estado`
--

CREATE TABLE `drives_estado` (
  `ID_ESTADO` int(11) NOT NULL,
  `ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `fonte`
--

CREATE TABLE `fonte` (
  `FONTE_ID` int(11) NOT NULL,
  `FONTE_REF` varchar(100) NOT NULL,
  `FONTE_MARCA` varchar(100) NOT NULL,
  `FONTE_MODELO` varchar(100) NOT NULL,
  `FONTE_COR` varchar(100) NOT NULL,
  `FONTE_COMP` varchar(100) NOT NULL DEFAULT '',
  `FONTE_POTENCIA` varchar(100) NOT NULL,
  `FONTE_FREQUENCIA` varchar(100) NOT NULL,
  `FONTE_SALA` varchar(100) NOT NULL DEFAULT '',
  `FONTE_ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `fontefrequencia`
--

CREATE TABLE `fontefrequencia` (
  `FONTE_FREQUENCIA` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `fontemodelo`
--

CREATE TABLE `fontemodelo` (
  `FONTE_MODELO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `fontepotencia`
--

CREATE TABLE `fontepotencia` (
  `FONTE_POTENCIA` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `fontes_estado`
--

CREATE TABLE `fontes_estado` (
  `ID_ESTADO` int(11) NOT NULL,
  `ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `marcacaixa`
--

CREATE TABLE `marcacaixa` (
  `MC_ID` int(11) NOT NULL,
  `MC_NOME` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `marcacooler`
--

CREATE TABLE `marcacooler` (
  `MCO_ID` int(11) NOT NULL,
  `MCO_NOME` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `marcadisco`
--

CREATE TABLE `marcadisco` (
  `MDSC_ID` int(11) NOT NULL,
  `MDSC_NOME` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `marcadrdvd`
--

CREATE TABLE `marcadrdvd` (
  `MD_ID` int(11) NOT NULL,
  `MD_NOME` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `marcafonte`
--

CREATE TABLE `marcafonte` (
  `MF_ID` int(11) NOT NULL,
  `MF_NOME` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `marcamonitor`
--

CREATE TABLE `marcamonitor` (
  `MM_ID` int(11) NOT NULL,
  `MM_NOME` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `marcamr`
--

CREATE TABLE `marcamr` (
  `MMR_ID` int(11) NOT NULL,
  `MMR_NOME` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `marcapg`
--

CREATE TABLE `marcapg` (
  `MPG_ID` int(11) NOT NULL,
  `MPG_NOME` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `marcapm`
--

CREATE TABLE `marcapm` (
  `MPM_ID` int(11) NOT NULL,
  `MPM_NOME` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `marcapr`
--

CREATE TABLE `marcapr` (
  `MPR_ID` int(11) NOT NULL,
  `MPR_NOME` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `marcaproc`
--

CREATE TABLE `marcaproc` (
  `MP_ID` int(11) NOT NULL,
  `MP_NOME` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `marcarato`
--

CREATE TABLE `marcarato` (
  `MRATO_ID` int(11) NOT NULL,
  `MRATO_NOME` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `marcateclado`
--

CREATE TABLE `marcateclado` (
  `MTECLADO_ID` int(11) NOT NULL,
  `MTECLADO_NOME` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `marcaventoinha`
--

CREATE TABLE `marcaventoinha` (
  `MV_ID` int(11) NOT NULL,
  `MV_NOME` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `memoriaram`
--

CREATE TABLE `memoriaram` (
  `MR_ID` int(11) NOT NULL,
  `MR_REF` varchar(100) NOT NULL,
  `MR_MARCA` varchar(100) NOT NULL,
  `MR_TIPO` varchar(100) NOT NULL,
  `MR_MODELO` varchar(100) NOT NULL,
  `MR_COR` varchar(100) NOT NULL,
  `MR_VELOCIDADE` varchar(100) NOT NULL,
  `MR_COMP` varchar(100) NOT NULL DEFAULT '',
  `MR_SALA` varchar(100) NOT NULL DEFAULT '',
  `MR_ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `memoriaram_estado`
--

CREATE TABLE `memoriaram_estado` (
  `ID_ESTADO` int(11) NOT NULL,
  `ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `memoriarmodelo`
--

CREATE TABLE `memoriarmodelo` (
  `MR_MODELO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `memoriartipo`
--

CREATE TABLE `memoriartipo` (
  `MR_TIPO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `memoriarvelocidade`
--

CREATE TABLE `memoriarvelocidade` (
  `MR_VELOCIDADE` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `monitor`
--

CREATE TABLE `monitor` (
  `MONITOR_ID` int(11) NOT NULL,
  `MONITOR_REF` varchar(100) NOT NULL,
  `MONITOR_MARCA` varchar(100) NOT NULL,
  `MONITOR_POL` varchar(100) NOT NULL,
  `MONITOR_FREQ` varchar(100) NOT NULL,
  `MONITOR_COR` varchar(100) NOT NULL,
  `MONITOR_SALA` varchar(100) NOT NULL DEFAULT '',
  `MONITOR_COMP` varchar(100) NOT NULL DEFAULT '',
  `MONITOR_ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `monitores_estado`
--

CREATE TABLE `monitores_estado` (
  `ID_ESTADO` int(11) NOT NULL,
  `ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `monitorfrequencia`
--

CREATE TABLE `monitorfrequencia` (
  `MONITOR_FREQUENCIA` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `monitorpolegada`
--

CREATE TABLE `monitorpolegada` (
  `MONITOR_POL` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `pgmemoria`
--

CREATE TABLE `pgmemoria` (
  `PG_MEMORIA` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `pgmodelo`
--

CREATE TABLE `pgmodelo` (
  `PG_MODELO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `placaderede`
--

CREATE TABLE `placaderede` (
  `PR_ID` int(11) NOT NULL,
  `PR_REF` varchar(100) NOT NULL,
  `PR_MARCA` varchar(100) NOT NULL,
  `PG_COR` varchar(100) NOT NULL,
  `PR_LR` varchar(100) NOT NULL,
  `PR_COMP` varchar(100) NOT NULL DEFAULT '',
  `PR_SALA` varchar(100) NOT NULL DEFAULT '',
  `PR_ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `placagrafica`
--

CREATE TABLE `placagrafica` (
  `PG_ID` int(11) NOT NULL,
  `PG_REF` varchar(100) NOT NULL,
  `PG_MARCA` varchar(100) NOT NULL,
  `PG_MEMORIA` varchar(100) NOT NULL,
  `PG_MODELO` varchar(100) NOT NULL,
  `PG_COR` varchar(100) NOT NULL,
  `PG_COMP` varchar(100) NOT NULL DEFAULT '',
  `PG_SALA` varchar(100) NOT NULL DEFAULT '',
  `PG_ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `placagrafica_estado`
--

CREATE TABLE `placagrafica_estado` (
  `ID_ESTADO` int(11) NOT NULL,
  `ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `placamae`
--

CREATE TABLE `placamae` (
  `PM_ID` int(11) NOT NULL,
  `PM_REF` varchar(100) NOT NULL,
  `PM_MARCA` varchar(100) NOT NULL,
  `PM_TAM` varchar(100) NOT NULL,
  `PM_SOCKET` varchar(100) NOT NULL,
  `PM_CHIPSET` varchar(100) NOT NULL,
  `PM_COR` varchar(100) NOT NULL,
  `PM_FORMATO` varchar(100) NOT NULL,
  `PM_TMEMORIA` varchar(100) NOT NULL,
  `PM_ESTADO` varchar(100) NOT NULL,
  `PM_COMP` varchar(100) NOT NULL DEFAULT '',
  `PM_SALA` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `placamae_estado`
--

CREATE TABLE `placamae_estado` (
  `ID_ESTADO` int(11) NOT NULL,
  `ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `placarede_estado`
--

CREATE TABLE `placarede_estado` (
  `ID_ESTADO` int(11) NOT NULL,
  `ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `pmchipset`
--

CREATE TABLE `pmchipset` (
  `PM_CHIPSET` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `pmformato`
--

CREATE TABLE `pmformato` (
  `PM_FORMATO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `pmsocket`
--

CREATE TABLE `pmsocket` (
  `PM_SOCKET` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `pmtam`
--

CREATE TABLE `pmtam` (
  `PM_TAM` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `pmtmemoria`
--

CREATE TABLE `pmtmemoria` (
  `PM_TMEMORIA` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `prlrede`
--

CREATE TABLE `prlrede` (
  `PR_LR` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `proccore`
--

CREATE TABLE `proccore` (
  `PROC_CORE` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `processador`
--

CREATE TABLE `processador` (
  `PROC_ID` int(11) NOT NULL,
  `PROC_REF` varchar(100) NOT NULL,
  `PROC_MARCA` varchar(100) NOT NULL,
  `PROC_MODELO` varchar(100) NOT NULL,
  `PROC_CORE` int(11) NOT NULL,
  `PROC_COR` varchar(100) NOT NULL,
  `PROC_THREADS` varchar(100) NOT NULL,
  `PROC_SOCKET` varchar(100) NOT NULL,
  `PROC_ESTADO` varchar(100) NOT NULL,
  `PROC_COMP` varchar(100) NOT NULL DEFAULT '',
  `PROC_SALA` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `processadores_estado`
--

CREATE TABLE `processadores_estado` (
  `ID_ESTADO` int(11) NOT NULL,
  `ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `procmodelo`
--

CREATE TABLE `procmodelo` (
  `PROC_MODELO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `procsocket`
--

CREATE TABLE `procsocket` (
  `PROC_SOCKET` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `procthreads`
--

CREATE TABLE `procthreads` (
  `PROC_THREADS` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `ratos`
--

CREATE TABLE `ratos` (
  `RATOS_ID` int(11) NOT NULL,
  `RATOS_REF` varchar(100) NOT NULL,
  `RATO_MARCA` varchar(100) NOT NULL,
  `RATOS_WIR` varchar(100) NOT NULL,
  `RATOS_COR` varchar(100) NOT NULL,
  `RATOS_COMP` varchar(100) NOT NULL DEFAULT '',
  `RATOS_SALA` varchar(100) NOT NULL DEFAULT '',
  `RATOS_ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `ratowir`
--

CREATE TABLE `ratowir` (
  `RATO_WIR` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rato_estado`
--

CREATE TABLE `rato_estado` (
  `ID_ESTADO` int(11) NOT NULL,
  `ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `recuperacao_senha`
--

CREATE TABLE `recuperacao_senha` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo_usuario` enum('comunidade_escolar','tecnico') NOT NULL,
  `token` varchar(64) NOT NULL,
  `validade` datetime NOT NULL,
  `usado` tinyint(1) DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `salas`
--

CREATE TABLE `salas` (
  `id_sala` int(11) NOT NULL,
  `sala` varchar(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `suporte_tecnico`
--

CREATE TABLE `suporte_tecnico` (
  `id` int(11) NOT NULL,
  `nome_completo` varchar(255) NOT NULL,
  `email_institucional` varchar(255) NOT NULL,
  `departamento_tecnico` enum('suporte','manutencao','administrador') NOT NULL,
  `numero_processo` varchar(50) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `primeiro_login` tinyint(1) DEFAULT 1,
  `foto_perfil` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `teclados`
--

CREATE TABLE `teclados` (
  `TECLADO_ID` int(11) NOT NULL,
  `TECLADO_REF` varchar(100) NOT NULL,
  `TECLADO_MARCA` varchar(100) NOT NULL,
  `TECLADO_COR` varchar(100) NOT NULL,
  `TECLADO_ESTADO` varchar(100) NOT NULL,
  `TECLADO_SALA` varchar(100) NOT NULL DEFAULT '',
  `TECLADO_TIPO` varchar(100) DEFAULT NULL,
  `TECLADO_COMP` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `teclados_estado`
--

CREATE TABLE `teclados_estado` (
  `ID_ESTADO` int(11) NOT NULL,
  `ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tecladotipo`
--

CREATE TABLE `tecladotipo` (
  `TECLADO_TIPO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `assunto` varchar(50) NOT NULL,
  `sala` varchar(50) NOT NULL,
  `entrada` time NOT NULL,
  `solicitante` varchar(100) NOT NULL,
  `prioridade` enum('1','2','3') NOT NULL,
  `status` enum('aberto','em_andamento','resolvido','fechado') DEFAULT 'aberto',
  `descricao` text NOT NULL,
  `mensagem_erro` text NOT NULL,
  `observacoes_adicionais` text NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_tecnico` int(11) DEFAULT NULL,
  `criado_em` datetime DEFAULT current_timestamp(),
  `ultima_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `ticket_anexos`
--

CREATE TABLE `ticket_anexos` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `nome_arquivo` varchar(255) NOT NULL,
  `tipo_arquivo` varchar(100) NOT NULL,
  `tamanho` int(11) NOT NULL,
  `caminho` varchar(255) NOT NULL,
  `data_upload` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `ticket_comentarios`
--

CREATE TABLE `ticket_comentarios` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `tecnico_id` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `data_comentario` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `ticket_componentes`
--

CREATE TABLE `ticket_componentes` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `equipamento` varchar(50) NOT NULL,
  `ref_equipamento` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `ticket_historico`
--

CREATE TABLE `ticket_historico` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `tecnico_id` int(11) DEFAULT NULL,
  `acao` varchar(100) NOT NULL COMMENT 'criado, atualizado, status alterado, etc.',
  `descricao` text NOT NULL,
  `status_anterior` varchar(50) DEFAULT NULL,
  `status_novo` varchar(50) DEFAULT NULL,
  `data_acao` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `ventoinha`
--

CREATE TABLE `ventoinha` (
  `VENT_ID` int(11) NOT NULL,
  `VENT_REF` varchar(100) NOT NULL,
  `VENT_MARCA` varchar(100) NOT NULL,
  `VENT_TAM` varchar(100) NOT NULL,
  `VENT_ESTADO` varchar(100) NOT NULL,
  `VENT_COR` varchar(100) NOT NULL,
  `VENT_COMP` varchar(100) NOT NULL DEFAULT '',
  `VENT_SALA` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `ventoinhas_estado`
--

CREATE TABLE `ventoinhas_estado` (
  `ID_ESTADO` int(11) NOT NULL,
  `ESTADO` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `ventoinhatamanho`
--

CREATE TABLE `ventoinhatamanho` (
  `VENT_TAM` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `caixa`
--
ALTER TABLE `caixa`
  ADD PRIMARY KEY (`CAIXA_ID`);

--
-- Índices para tabela `caixas_estado`
--
ALTER TABLE `caixas_estado`
  ADD PRIMARY KEY (`ID_ESTADO`);

--
-- Índices para tabela `comentarios`
--
ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_ticket` (`id_ticket`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices para tabela `comp_estado`
--
ALTER TABLE `comp_estado`
  ADD PRIMARY KEY (`ID_ESTADO`);

--
-- Índices para tabela `comunidade_escolar`
--
ALTER TABLE `comunidade_escolar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_institucional` (`email_institucional`),
  ADD UNIQUE KEY `numero_processo` (`numero_processo`);

--
-- Índices para tabela `cooler`
--
ALTER TABLE `cooler`
  ADD PRIMARY KEY (`COO_ID`);

--
-- Índices para tabela `coolers_estado`
--
ALTER TABLE `coolers_estado`
  ADD PRIMARY KEY (`ID_ESTADO`);

--
-- Índices para tabela `cor`
--
ALTER TABLE `cor`
  ADD PRIMARY KEY (`ID_COR`);

--
-- Índices para tabela `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `disciplinas`
--
ALTER TABLE `disciplinas`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `disco`
--
ALTER TABLE `disco`
  ADD PRIMARY KEY (`DISCO_ID`);

--
-- Índices para tabela `discos_estado`
--
ALTER TABLE `discos_estado`
  ADD PRIMARY KEY (`ID_ESTADO`);

--
-- Índices para tabela `drivedvd`
--
ALTER TABLE `drivedvd`
  ADD PRIMARY KEY (`DRVD_ID`);

--
-- Índices para tabela `drives_estado`
--
ALTER TABLE `drives_estado`
  ADD PRIMARY KEY (`ID_ESTADO`);

--
-- Índices para tabela `fonte`
--
ALTER TABLE `fonte`
  ADD PRIMARY KEY (`FONTE_ID`);

--
-- Índices para tabela `fontes_estado`
--
ALTER TABLE `fontes_estado`
  ADD PRIMARY KEY (`ID_ESTADO`);

--
-- Índices para tabela `marcacaixa`
--
ALTER TABLE `marcacaixa`
  ADD PRIMARY KEY (`MC_ID`);

--
-- Índices para tabela `marcacooler`
--
ALTER TABLE `marcacooler`
  ADD PRIMARY KEY (`MCO_ID`);

--
-- Índices para tabela `marcadisco`
--
ALTER TABLE `marcadisco`
  ADD PRIMARY KEY (`MDSC_ID`);

--
-- Índices para tabela `marcadrdvd`
--
ALTER TABLE `marcadrdvd`
  ADD PRIMARY KEY (`MD_ID`);

--
-- Índices para tabela `marcafonte`
--
ALTER TABLE `marcafonte`
  ADD PRIMARY KEY (`MF_ID`);

--
-- Índices para tabela `marcamonitor`
--
ALTER TABLE `marcamonitor`
  ADD PRIMARY KEY (`MM_ID`);

--
-- Índices para tabela `marcamr`
--
ALTER TABLE `marcamr`
  ADD PRIMARY KEY (`MMR_ID`);

--
-- Índices para tabela `marcapg`
--
ALTER TABLE `marcapg`
  ADD PRIMARY KEY (`MPG_ID`);

--
-- Índices para tabela `marcapm`
--
ALTER TABLE `marcapm`
  ADD PRIMARY KEY (`MPM_ID`);

--
-- Índices para tabela `marcapr`
--
ALTER TABLE `marcapr`
  ADD PRIMARY KEY (`MPR_ID`);

--
-- Índices para tabela `marcaproc`
--
ALTER TABLE `marcaproc`
  ADD PRIMARY KEY (`MP_ID`);

--
-- Índices para tabela `marcarato`
--
ALTER TABLE `marcarato`
  ADD PRIMARY KEY (`MRATO_ID`);

--
-- Índices para tabela `marcateclado`
--
ALTER TABLE `marcateclado`
  ADD PRIMARY KEY (`MTECLADO_ID`);

--
-- Índices para tabela `marcaventoinha`
--
ALTER TABLE `marcaventoinha`
  ADD PRIMARY KEY (`MV_ID`);

--
-- Índices para tabela `memoriaram`
--
ALTER TABLE `memoriaram`
  ADD PRIMARY KEY (`MR_ID`);

--
-- Índices para tabela `memoriaram_estado`
--
ALTER TABLE `memoriaram_estado`
  ADD PRIMARY KEY (`ID_ESTADO`);

--
-- Índices para tabela `monitor`
--
ALTER TABLE `monitor`
  ADD PRIMARY KEY (`MONITOR_ID`);

--
-- Índices para tabela `monitores_estado`
--
ALTER TABLE `monitores_estado`
  ADD PRIMARY KEY (`ID_ESTADO`);

--
-- Índices para tabela `placaderede`
--
ALTER TABLE `placaderede`
  ADD PRIMARY KEY (`PR_ID`);

--
-- Índices para tabela `placagrafica`
--
ALTER TABLE `placagrafica`
  ADD PRIMARY KEY (`PG_ID`);

--
-- Índices para tabela `placagrafica_estado`
--
ALTER TABLE `placagrafica_estado`
  ADD PRIMARY KEY (`ID_ESTADO`);

--
-- Índices para tabela `placamae`
--
ALTER TABLE `placamae`
  ADD PRIMARY KEY (`PM_ID`);

--
-- Índices para tabela `placamae_estado`
--
ALTER TABLE `placamae_estado`
  ADD PRIMARY KEY (`ID_ESTADO`);

--
-- Índices para tabela `placarede_estado`
--
ALTER TABLE `placarede_estado`
  ADD PRIMARY KEY (`ID_ESTADO`);

--
-- Índices para tabela `processador`
--
ALTER TABLE `processador`
  ADD PRIMARY KEY (`PROC_ID`);

--
-- Índices para tabela `processadores_estado`
--
ALTER TABLE `processadores_estado`
  ADD PRIMARY KEY (`ID_ESTADO`);

--
-- Índices para tabela `ratos`
--
ALTER TABLE `ratos`
  ADD PRIMARY KEY (`RATOS_ID`);

--
-- Índices para tabela `rato_estado`
--
ALTER TABLE `rato_estado`
  ADD PRIMARY KEY (`ID_ESTADO`);

--
-- Índices para tabela `recuperacao_senha`
--
ALTER TABLE `recuperacao_senha`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_recuperacao_token` (`token`),
  ADD KEY `idx_recuperacao_usuario` (`usuario_id`,`tipo_usuario`);

--
-- Índices para tabela `salas`
--
ALTER TABLE `salas`
  ADD PRIMARY KEY (`id_sala`);

--
-- Índices para tabela `suporte_tecnico`
--
ALTER TABLE `suporte_tecnico`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_institucional` (`email_institucional`),
  ADD UNIQUE KEY `numero_processo` (`numero_processo`);

--
-- Índices para tabela `teclados`
--
ALTER TABLE `teclados`
  ADD PRIMARY KEY (`TECLADO_ID`);

--
-- Índices para tabela `teclados_estado`
--
ALTER TABLE `teclados_estado`
  ADD PRIMARY KEY (`ID_ESTADO`);

--
-- Índices para tabela `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_tecnico` (`id_tecnico`);

--
-- Índices para tabela `ticket_anexos`
--
ALTER TABLE `ticket_anexos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- Índices para tabela `ticket_comentarios`
--
ALTER TABLE `ticket_comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `tecnico_id` (`tecnico_id`);

--
-- Índices para tabela `ticket_componentes`
--
ALTER TABLE `ticket_componentes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- Índices para tabela `ticket_historico`
--
ALTER TABLE `ticket_historico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `tecnico_id` (`tecnico_id`);

--
-- Índices para tabela `ventoinha`
--
ALTER TABLE `ventoinha`
  ADD PRIMARY KEY (`VENT_ID`);

--
-- Índices para tabela `ventoinhas_estado`
--
ALTER TABLE `ventoinhas_estado`
  ADD PRIMARY KEY (`ID_ESTADO`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `caixa`
--
ALTER TABLE `caixa`
  MODIFY `CAIXA_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `caixas_estado`
--
ALTER TABLE `caixas_estado`
  MODIFY `ID_ESTADO` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `comp_estado`
--
ALTER TABLE `comp_estado`
  MODIFY `ID_ESTADO` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `comunidade_escolar`
--
ALTER TABLE `comunidade_escolar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cooler`
--
ALTER TABLE `cooler`
  MODIFY `COO_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `coolers_estado`
--
ALTER TABLE `coolers_estado`
  MODIFY `ID_ESTADO` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cor`
--
ALTER TABLE `cor`
  MODIFY `ID_COR` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `disciplinas`
--
ALTER TABLE `disciplinas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `disco`
--
ALTER TABLE `disco`
  MODIFY `DISCO_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `discos_estado`
--
ALTER TABLE `discos_estado`
  MODIFY `ID_ESTADO` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `drivedvd`
--
ALTER TABLE `drivedvd`
  MODIFY `DRVD_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `drives_estado`
--
ALTER TABLE `drives_estado`
  MODIFY `ID_ESTADO` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `fonte`
--
ALTER TABLE `fonte`
  MODIFY `FONTE_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `fontes_estado`
--
ALTER TABLE `fontes_estado`
  MODIFY `ID_ESTADO` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `marcacaixa`
--
ALTER TABLE `marcacaixa`
  MODIFY `MC_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `marcacooler`
--
ALTER TABLE `marcacooler`
  MODIFY `MCO_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `marcadisco`
--
ALTER TABLE `marcadisco`
  MODIFY `MDSC_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `marcadrdvd`
--
ALTER TABLE `marcadrdvd`
  MODIFY `MD_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `marcafonte`
--
ALTER TABLE `marcafonte`
  MODIFY `MF_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `marcamonitor`
--
ALTER TABLE `marcamonitor`
  MODIFY `MM_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `marcamr`
--
ALTER TABLE `marcamr`
  MODIFY `MMR_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `marcapg`
--
ALTER TABLE `marcapg`
  MODIFY `MPG_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `marcapm`
--
ALTER TABLE `marcapm`
  MODIFY `MPM_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `marcapr`
--
ALTER TABLE `marcapr`
  MODIFY `MPR_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `marcaproc`
--
ALTER TABLE `marcaproc`
  MODIFY `MP_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `marcarato`
--
ALTER TABLE `marcarato`
  MODIFY `MRATO_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `marcateclado`
--
ALTER TABLE `marcateclado`
  MODIFY `MTECLADO_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `marcaventoinha`
--
ALTER TABLE `marcaventoinha`
  MODIFY `MV_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `memoriaram`
--
ALTER TABLE `memoriaram`
  MODIFY `MR_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `memoriaram_estado`
--
ALTER TABLE `memoriaram_estado`
  MODIFY `ID_ESTADO` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `monitor`
--
ALTER TABLE `monitor`
  MODIFY `MONITOR_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `monitores_estado`
--
ALTER TABLE `monitores_estado`
  MODIFY `ID_ESTADO` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `placaderede`
--
ALTER TABLE `placaderede`
  MODIFY `PR_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `placagrafica`
--
ALTER TABLE `placagrafica`
  MODIFY `PG_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `placagrafica_estado`
--
ALTER TABLE `placagrafica_estado`
  MODIFY `ID_ESTADO` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `placamae`
--
ALTER TABLE `placamae`
  MODIFY `PM_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `placamae_estado`
--
ALTER TABLE `placamae_estado`
  MODIFY `ID_ESTADO` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `placarede_estado`
--
ALTER TABLE `placarede_estado`
  MODIFY `ID_ESTADO` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `processador`
--
ALTER TABLE `processador`
  MODIFY `PROC_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `processadores_estado`
--
ALTER TABLE `processadores_estado`
  MODIFY `ID_ESTADO` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ratos`
--
ALTER TABLE `ratos`
  MODIFY `RATOS_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `rato_estado`
--
ALTER TABLE `rato_estado`
  MODIFY `ID_ESTADO` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `recuperacao_senha`
--
ALTER TABLE `recuperacao_senha`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `salas`
--
ALTER TABLE `salas`
  MODIFY `id_sala` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `suporte_tecnico`
--
ALTER TABLE `suporte_tecnico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `teclados`
--
ALTER TABLE `teclados`
  MODIFY `TECLADO_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `teclados_estado`
--
ALTER TABLE `teclados_estado`
  MODIFY `ID_ESTADO` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ticket_anexos`
--
ALTER TABLE `ticket_anexos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ticket_comentarios`
--
ALTER TABLE `ticket_comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ticket_componentes`
--
ALTER TABLE `ticket_componentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ticket_historico`
--
ALTER TABLE `ticket_historico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ventoinha`
--
ALTER TABLE `ventoinha`
  MODIFY `VENT_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ventoinhas_estado`
--
ALTER TABLE `ventoinhas_estado`
  MODIFY `ID_ESTADO` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
