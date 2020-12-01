# Kimsufi Servers Availability

Script to notify the availability of [kimsufi servers](https://www.kimsufi.com/es/servidores.xml) by [Telegram](https://telegram.org/).

## Usage
1. **Clone** or **Download** this repository.

2. Copy file `config.php.dist` to `config.php`.
Modify with the your data.

3. Build the environment by running the `make start` command.
Once in the bash terminal, type `exit` to exit the environment.

4. Locate the reference of a [server](https://www.kimsufi.com/es/servidores.xml) that is currently available.
You can do this by viewing the HTML of the server line and copying the value of `data-ref`. For example: `1801sk17`.

5. Run the script to verify that everything works correctly: `make run`

6. **If the notifications have arrived correctly**, you can only indicate the correct server references and schedule a
crontab that executes the script every so often.

## License
Licensed under the [MIT license](http://opensource.org/licenses/MIT)

Read [LICENSE](LICENSE) for more information
