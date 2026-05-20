variable "aws_region" {
  type    = string
  default = "us-east-1"
}

variable "project_name" {
  type = string
}

variable "environment" {
  type = string
}

variable "db_name" {
  type    = string
  default = "ecommerce"
}

variable "db_username" {
  type    = string
  default = "ecommerce_user"
}

variable "db_password" {
  type      = string
  sensitive = true
}

variable "rds_instance_class" {
  type    = string
  default = "db.t4g.micro"
}

variable "elasticache_node_type" {
  type    = string
  default = "cache.t4g.micro"
}

variable "redis_auth_token" {
  type      = string
  sensitive = true
}
