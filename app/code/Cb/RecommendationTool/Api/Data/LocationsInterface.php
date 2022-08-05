<?php

namespace Cb\RecommendationTool\Api\Data;

interface LocationsInterface
{
	/**#@+
	 * Constants for keys of data array. Identical to the name of the getter in snake case
	 */
	const ID  = 'id';
	const FRONTEND_LABEL  = 'frontend_label';
	const BACKEND_LABEL  = 'backend_label';
	

	public function getId();
	public function getFrontendLabel();
	public function getBackendLabel();
  
	public function setId($id);
	public function setFrontendLabel($frontendLabel);
	public function setBackendLabel($backendLabel);

}