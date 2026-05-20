variable "project_name" {
  type = string
}

variable "environment" {
  type = string
}

variable "subnet_ids" {
  type = list(string)
}

variable "security_group_id" {
  type = string
}

variable "auth_token" {
  type      = string
  sensitive = true

  validation {
    condition     = length(var.auth_token) >= 16 && length(var.auth_token) <= 128 && !can(regex("[@\"/]", var.auth_token))
    error_message = "auth_token must be 16-128 characters and must not contain @, \", or /."
  }
}

variable "node_type" {
  type    = string
  default = "cache.t4g.micro"
}

variable "num_cache_clusters" {
  type    = number
  default = 1
}
