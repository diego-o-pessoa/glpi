@echo off
rem Duplo clique: instala o Ativa Unified Agent sem perguntas (pede apenas a elevacao do Windows).
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0Deploy-AtivaUnifiedAgent.ps1" %*
exit /b %errorlevel%
