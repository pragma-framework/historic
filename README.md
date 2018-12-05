# Pragma Historic

The Pragma Module for handling models changelogs in Pragma Framework.

## Installation

In composer.json add:

	require {"pragma-framework/historic": "dev-master"}

## Howto use with Pragma/Model classes

In Model, add `use Historisable;` and in Model::\_\__construct `$this->set_historised(true);`

In Model::delete() add `this->set_global_name($this->field)` (DEPRECATED)

In Model::\_\_construct add `$this->set_global_name_fields(['field']);`


## What about created_at and created_by

These columns should be handled by within the PRAGMA_HISTORIC_CREATION_HOOK constant (in the config.php)
