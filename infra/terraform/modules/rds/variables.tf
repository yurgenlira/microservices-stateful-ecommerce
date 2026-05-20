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

variable "db_name" {
  type    = string
  default = "app"
}

variable "db_username" {
  type    = string
  default = "app_user"
}

variable "db_password" {
  type      = string
  sensitive = true
}

variable "instance_class" {
  type    = string
  default = "db.t4g.micro"
}

variable "allocated_storage" {
  type    = number
  default = 20
}

variable "multi_az" {
  type    = bool
  default = false
}

variable "backup_retention_days" {
  type    = number
  default = 7
}
