#!/bin/bash
export FLASK_MODE=online
gunicorn api_flask:app
