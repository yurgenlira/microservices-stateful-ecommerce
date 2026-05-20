variable "project_name" {
  type = string
}

variable "environment" {
  type = string
}

variable "aws_region" {
  type = string
}

variable "ecr_repository_url" {
  type = string
}

variable "private_subnet_ids" {
  type = list(string)
}

variable "app_security_group_id" {
  type = string
}

variable "cpu" {
  type    = string
  default = "1024"
}

variable "memory" {
  type    = string
  default = "2048"
}

variable "environment_variables" {
  type    = map(string)
  default = {}
}

variable "environment_secrets" {
  type      = map(string)
  sensitive = true
  default   = {}
}