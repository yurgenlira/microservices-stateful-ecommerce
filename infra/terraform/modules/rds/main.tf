resource "aws_db_subnet_group" "this" {
  name       = "${var.project_name}-${var.environment}"
  subnet_ids = var.subnet_ids
}

resource "aws_db_parameter_group" "this" {
  name   = "${var.project_name}-${var.environment}-pg18"
  family = "postgres18"

  parameter {
    name         = "log_min_duration_statement"
    value        = "500"
    apply_method = "immediate"
  }

  parameter {
    name         = "log_connections"
    value        = "all"
    apply_method = "immediate"
  }
}

resource "aws_db_instance" "this" {
  identifier        = "${var.project_name}-${var.environment}"
  engine            = "postgres"
  engine_version    = "18"
  instance_class    = var.instance_class
  allocated_storage = var.allocated_storage
  storage_type      = "gp3"
  storage_encrypted = true

  db_name  = var.db_name
  username = var.db_username
  password = var.db_password

  db_subnet_group_name   = aws_db_subnet_group.this.name
  vpc_security_group_ids = [var.security_group_id]
  parameter_group_name   = aws_db_parameter_group.this.name

  backup_retention_period = var.backup_retention_days
  backup_window           = "03:00-04:00"
  maintenance_window      = "Mon:04:00-Mon:05:00"
  multi_az                = var.multi_az
  deletion_protection     = var.environment != "dev"
  skip_final_snapshot     = var.environment == "dev"
  publicly_accessible     = false

  performance_insights_enabled          = true
  performance_insights_retention_period = 7

  apply_immediately = var.environment == "dev"
}
