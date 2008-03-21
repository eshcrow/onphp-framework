<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Ivan Y. Khvostishkov                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Primitives
	**/
	class PrimitiveForm extends BasePrimitive
	{
		protected $proto = null;
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveForm
		 * 
		 * @deprecated You should use ofProto() instead
		**/
		public function of($className)
		{
			Assert::isTrue(
				class_exists($className, true),
				"knows nothing about '{$className}' class"
			);
			
			$protoClass = DTOProto::PROTO_CLASS_PREFIX.$className;
			
			Assert::isTrue(
				class_exists($protoClass, true),
				"knows nothing about '{$protoClass}' class"
			);
			
			return $this->ofProto(Singleton::getInstance($protoClass));
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveForm
		**/
		public function ofProto(DTOProto $proto)
		{
			$this->proto = $proto;
			
			return $this;
		}
		
		public function getClassName()
		{
			return $this->proto->className();
		}
		
		public function getProto()
		{
			return $this->proto;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveForm
		**/
		public function setValue($value)
		{
			Assert::isTrue($value instanceof Form);
			
			return parent::setValue($value);
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveForm
		**/
		public function importValue($value)
		{
			if ($value === null)
				return $this->value = null;
			
			Assert::isTrue($value instanceof Form);
			
			$this->value = $value;
			
			return ($value->getErrors() ? false : true);
		}
		
		public function exportValue()
		{
			if (!$this->value)
				return null;
			
			return $this->value->export();
		}
		
		public function import($scope, $prefix = null)
		{
			return $this->actualImport($scope, true, $prefix);
		}
		
		public function unfilteredImport($scope, $prefix = null)
		{
			return $this->actualImport($scope, false, $prefix);
		}
		
		private function actualImport($scope, $importFiltering, $prefix = null)
		{
			$name = $this->getActualName($prefix);
			
			if (!$this->proto)
				throw new WrongStateException(
					"no proto defined for PrimitiveForm '{$name}'"
				);
			
			if (!isset($scope[$name]))
				return null;
			
			$this->rawValue = $scope[$name];
			
			$this->value = $this->proto->makeForm();
			
			if (!$importFiltering) {
				$this->value->
					disableImportFiltering()->
					import($this->rawValue)->
					enableImportFiltering();
			} else {
				$this->value->import($this->rawValue);
			}
			
			$this->imported = true;
			
			if ($this->value->getErrors())
				return false;
			
			return true;
		}
	}
?>