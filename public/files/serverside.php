<?php
class TableData
{
	public function __construct($dbuser = "app_prod", $dbpass = "HHFRD_JU")
	{
		$options = [
			PDO::ATTR_CASE => PDO::CASE_LOWER,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_ORACLE_NULLS => PDO::NULL_TO_STRING,
		];

		try {
			$this->conn = new PDO("oci:dbname=(DESCRIPTION =
            (ADDRESS = (PROTOCOL = TCP)(HOST = 172.18.0.212)(PORT=1550))
            (SERVER = SHARED)
            (CONNECT_DATA = (SID = dbprd1))
		);charset=UTF8", $dbuser, $dbpass, $options);
		} catch (PDOException $e) {
			return null;
		}
	}
	public function get($sTable, $sIndexColumn, $aColumns)
	{
		/*Paginado*/
		$sLimit = "";
		if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
			$sLimit = "WHERE rowsNumerator BETWEEN :iDisplayStart AND :iDisplayEnd";
		}

		/*Ordenamiento*/
		$sOrder = "";
		if (isset($_GET['iSortCol_0'])) {
			$sOrder = "ORDER BY ";
			//Va atravez de todas las columnas
			for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
				//Si se necesita filtrar la columna actual
				if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
					$sOrder .= $aColumns[intval($_GET['iSortCol_' . $i])];
					//Determina si esta ordenado de forma descentente o ascendente
					if (strcasecmp(($_GET['sSortDir_' . $i]), "asc") == 0) {
						$sOrder .= " asc, ";
					} else {
						$sOrder .= " desc, ";
					}
				}
			}

			//Eliminar el ultimo espacio ,
			$sOrder = substr_replace($sOrder, "", -2);

			//Revisa si hay una clausula de ordenamiento
			if ($sOrder == "ORDER BY") {
				$sOrder = "ORDER BY " . $sIndexColumn;
			}
		}

		/*
		* Filtración
		* NOTA: esto no coincide con el filtrado integrado de DataTables que sí lo hace.
		* palabra por palabra en cualquier campo. Es posible hacerlo aquí, pero preocupado por la eficiencia.
		*/
		$sWhere = "";
		$nWhereGenearalCount = 0;
		if (isset($_GET['sSearch'])) {
			$sWhereGenearal = $_GET['sSearch'];
		} else {
			$sWhereGenearal = '';
		}

		if ($_GET['sSearch'] != "") {
			//Establecer una cláusula where predeterminada para que la cláusula where no falle
			//en los casos en que no hay columnas que se puedan buscar en absoluto.
			$sWhere = "WHERE (";
			for ($i = 0; $i < count($aColumns) + 1; $i++) {
				//Si la columna actual tiene un parámetro de búsqueda
				if ($_GET['bSearchable_' . $i] == "true") {
					//Agregua la búsqueda a la cláusula where
					$sWhere .= $aColumns[$i] . " LIKE '%" . $_GET['sSearch'] . "%' OR ";
					$nWhereGenearalCount += 1;
				}
			}
			$sWhere = substr_replace($sWhere, "", -3);
			$sWhere .= ')';
		}

		/* Filtrado individual de columnas */
		$sWhereSpecificArray = array();
		$sWhereSpecificArrayCount = 0;
		for ($i = 0; $i < count($aColumns); $i++) {
			if ($_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
				//Si no habia clausula
				if ($sWhere == "") {
					$sWhere = "WHERE ";
				} else {
					$sWhere .= " AND ";
				}

				//Agrega la clausula de la columna especifica al where
				$sWhere .= $aColumns[$i] . " LIKE '%' || :whereSpecificParam" . $sWhereSpecificArrayCount . " || '%' ";

				//Es necesario para el enlace var
				$sWhereSpecificArrayCount++;

				//Agregua el parámetro de búsqueda actual
				$sWhereSpecificArray[] =  $_GET['sSearch_' . $i];
			}
		}

		// Si todavia no hay clausula pone una general que siempre es true
		if ($sWhere == "") {
			$sWhere = "WHERE 1=1";
		}

		/*SQL querys*/
		//Sql principal no ha sido filtrada ni modificada.
		$sQueryInner = "SELECT " . implode(', ', $aColumns) . ", row_number() over (" . $sOrder . ") rowsNumerator FROM   " . $sTable . " " . $sWhere;
		$sQueryFinal = "SELECT " . implode(', ', $aColumns) . " FROM (" . $sQueryInner . ") qry " . $sLimit . " ORDER BY rowsNumerator";

		/*Longitud del conjunto de datos después del filtrado*/
		$sQueryFinalCount = "SELECT COUNT(*) as \"totalRowsCount\" FROM (" . $sQueryInner . ") qry";

		$iFilteredTotal = 0;

		/*Longitud total de los datos*/
		$sQueryTotalCount = "SELECT COUNT(" . $sIndexColumn . ") as \"totalRowsCount\" FROM  " . $sTable;

		//Crea los Statments
		$statmntFinal = $this->conn->prepare($sQueryFinal);
		$statmntFinalCount = $this->conn->prepare($sQueryFinalCount);
		$statmntTotalCount = $this->conn->prepare($sQueryTotalCount);

		//Agrega las variables
		if (isset($_GET['iDisplayStart'])) {
			$dsplyStart = $_GET['iDisplayStart'];
		} else {
			$dsplyStart = 0;
		}

		if (isset($_GET['iDisplayLength']) && $_GET['iDisplayLength'] != '-1') {
			$dsplyRange = $_GET['iDisplayLength'];
			if ($dsplyRange > (2147483645 - intval($dsplyStart))) {
				$dsplyRange = 2147483645;
			} else {
				$dsplyRange = intval($dsplyStart) +  intval($dsplyRange);
			}
		} else {
			$dsplyRange = 2147483645;
		}

		//Agrega las variables a un numero de filas para ordenar
		$statmntFinal->bindValue(':iDisplayStart', $dsplyStart, PDO::PARAM_INT);
		$statmntFinal->bindValue(':iDisplayEnd', $dsplyRange, PDO::PARAM_INT);

		//Agrega las variables a una busqueda en especifica
		for ($i = 0; $i < $nWhereGenearalCount; $i++) {
			$statmntFinal->bindValue(':whereParam' . $i, $sWhereGenearal, PDO::PARAM_STR);
			$statmntFinalCount->bindValue(':whereParam' . $i, $sWhereGenearal, PDO::PARAM_STR);
		}

		//Agrega las variables a una busqueda en especifica
		for ($i = 0; $i < count($sWhereSpecificArray); $i++) {
			$statmntFinal->bindValue(':whereSpecificParam ' . $i, $sWhereSpecificArray[$i], PDO::PARAM_STR);
			$statmntFinalCount->bindValue(':whereSpecificParam ' . $i, $sWhereSpecificArray[$i], PDO::PARAM_STR);
		}

		//Se ejecutan los select
		$statmntTotalCount->execute();
		$iTotal = $statmntTotalCount->fetch(PDO::FETCH_ASSOC);

		$statmntFinalCount->execute();
		$iFilteredTotal = $statmntFinalCount->fetch(PDO::FETCH_ASSOC);

		$statmntFinal->execute();
		$rResult = $statmntFinal->fetchAll();

		/*Salida*/
		$output = array(
			"sEcho" => intval($_GET['sEcho']),
			"iTotalRecords" => $iTotal['totalrowscount'],
			"iTotalDisplayRecords" => $iFilteredTotal['totalrowscount'],
			"aaData" => array()
		);
		
		foreach ($rResult as $aRow) {
			$row = array();
			for ($i = 0; $i < count($aColumns); $i++) {
				if ($aColumns[$i] == "version") {
					/* Informacion formateada */
					$row[] = ($aRow[$aColumns[$i]] == "0") ? '-' : $aRow[$aColumns[$i]];
				} else if ($aColumns[$i] != ' ') {
					/* Informacion */
					$row[] = $aRow[$aColumns[$i]];
				}
			}
			$output['aaData'][] = $row;
		}
		echo json_encode($output);
	}
}
header('Pragma: no-cache');
header('Cache-Control: no-store, no-cache, must-revalidate');

// Crea una instancia de la tabla nueva
$table_data = new TableData();
