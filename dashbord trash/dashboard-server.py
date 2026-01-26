#!/usr/bin/env python3
"""
Serveur Python pour sauvegarder les données du Dashboard AECS
Écoute les requêtes POST et sauvegarde en JSON
"""

from http.server import HTTPServer, BaseHTTPRequestHandler
import json
import os
from datetime import datetime
from urllib.parse import urlparse

# Chemin où sauvegarder les données
DATA_FILE = '/share/Web/dashboard-data.json'
PORT = 8888

class DashboardHandler(BaseHTTPRequestHandler):
    
    def do_GET(self):
        """Répondre aux requêtes GET (charger les données)"""
        if self.path == '/api/data':
            self.send_response(200)
            self.send_header('Content-Type', 'application/json')
            self.send_header('Access-Control-Allow-Origin', '*')
            self.end_headers()
            
            try:
                if os.path.exists(DATA_FILE):
                    with open(DATA_FILE, 'r', encoding='utf-8') as f:
                        data = f.read()
                    self.wfile.write(data.encode('utf-8'))
                    print(f"✅ GET {datetime.now()}: Données envoyées")
                else:
                    # Fichier n'existe pas encore, retourner structure vide
                    empty_data = {
                        "activities": [],
                        "coordActivities": []
                    }
                    self.wfile.write(json.dumps(empty_data).encode('utf-8'))
                    print(f"📝 GET {datetime.now()}: Fichier n'existe pas, structure vide envoyée")
            except Exception as e:
                self.send_response(500)
                self.end_headers()
                print(f"❌ GET Error: {e}")
        else:
            self.send_response(404)
            self.end_headers()
    
    def do_POST(self):
        """Répondre aux requêtes POST (sauvegarder les données)"""
        if self.path == '/api/save':
            content_length = int(self.headers.get('Content-Length', 0))
            body = self.rfile.read(content_length)
            
            try:
                data = json.loads(body.decode('utf-8'))
                
                # Sauvegarder dans le fichier JSON
                with open(DATA_FILE, 'w', encoding='utf-8') as f:
                    json.dump(data, f, indent=2, ensure_ascii=False)
                
                # Répondre avec succès
                self.send_response(200)
                self.send_header('Content-Type', 'application/json')
                self.send_header('Access-Control-Allow-Origin', '*')
                self.end_headers()
                
                response = {
                    "status": "success",
                    "message": "Données sauvegardées sur le NAS",
                    "timestamp": datetime.now().isoformat()
                }
                self.wfile.write(json.dumps(response).encode('utf-8'))
                print(f"💾 POST {datetime.now()}: Données sauvegardées avec succès")
                
            except json.JSONDecodeError as e:
                self.send_response(400)
                self.send_header('Content-Type', 'application/json')
                self.send_header('Access-Control-Allow-Origin', '*')
                self.end_headers()
                response = {"status": "error", "message": "JSON invalide"}
                self.wfile.write(json.dumps(response).encode('utf-8'))
                print(f"❌ POST {datetime.now()}: Erreur JSON - {e}")
            
            except Exception as e:
                self.send_response(500)
                self.send_header('Content-Type', 'application/json')
                self.send_header('Access-Control-Allow-Origin', '*')
                self.end_headers()
                response = {"status": "error", "message": str(e)}
                self.wfile.write(json.dumps(response).encode('utf-8'))
                print(f"❌ POST {datetime.now()}: Erreur serveur - {e}")
        else:
            self.send_response(404)
            self.end_headers()
    
    def do_OPTIONS(self):
        """Gérer les requêtes CORS preflight"""
        self.send_response(200)
        self.send_header('Access-Control-Allow-Origin', '*')
        self.send_header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
        self.send_header('Access-Control-Allow-Headers', 'Content-Type')
        self.end_headers()
    
    def log_message(self, format, *args):
        """Supprimer les logs par défaut (nous gérons les nôtres)"""
        pass

if __name__ == '__main__':
    server = HTTPServer(('0.0.0.0', PORT), DashboardHandler)
    print(f"""
    ╔════════════════════════════════════════════════════════════╗
    ║     🐍 Serveur Dashboard AECS démarré                      ║
    ║     Port: {PORT}                                            ║
    ║     API GET:  http://192.168.1.100:{PORT}/api/data         ║
    ║     API POST: http://192.168.1.100:{PORT}/api/save         ║
    ║     Données: {DATA_FILE}                 ║
    ╚════════════════════════════════════════════════════════════╝
    """)
    
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        print("\n🛑 Serveur arrêté")
        server.server_close()