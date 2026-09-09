FROM python:3.10-slim
WORKDIR /app
COPY app.py /app/
COPY templates/ /app/templates/
RUN pip install flask requests
EXPOSE 5000
CMD ["python", "app.py"]
